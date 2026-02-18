use std::fs;

use local_ip_address::local_ip;

use crate::{
    cmd::{Prompt, execute_command},
    error::Error,
    network::{dns::collect_dns, *},
};

pub fn set_dhcp() -> Result<String, Error> {
    let mut prompt = Prompt::new();
    let default_path = "/etc/netplan/00-installer-config.yaml";
    let config_path =
        prompt.readline_with_default(&format!("config path [{}]: ", default_path), &default_path);

    let dns_list = collect_dns();
    let nameservers = if dns_list.is_empty() {
        None
    } else {
        Some(NameServers {
            addresses: Some(dns_list),
        })
    };

    let conf = Config {
        network: Network {
            ethernets: Ethernets {
                enp0s3: Enp0s {
                    dhcp4: true,
                    addresses: None,
                    gateway4: None,
                    nameservers,
                },
            },
            version: 2,
        },
    };

    let yaml = serde_yaml::to_string(&conf).map_err(|err| Error {
        error: vec![Box::new(err)],
        error_on: "set_dhcp",
        error_while: "convert config to yaml",
    })?;

    fs::write(&config_path, &yaml).map_err(|err| Error {
        error: vec![Box::new(err)],
        error_on: "set_dhcp",
        error_while: "write yaml to config file",
    })?;

    execute_command(&mut vec!["netplan", "apply"], "set_dhcp", "apply netplan")?;
    execute_command(&mut vec!["dhclient", "-r"], "set_dhcp", "clear connection")?;
    execute_command(&mut vec!["dhclient"], "set_dhcp", "get dhcp client")?;

    let new_ip = local_ip().unwrap();
    Ok(format!(
        "Successfully set network to DHCP\nDHCP IP: {}",
        new_ip
    ))
}
