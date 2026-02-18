use std::fs;

use local_ip_address::local_ip;

use crate::{
    cmd::{execute_command, Prompt},
    error::Error,
    log::{log_step, log_warn},
    network::{dns::collect_dns, *},
};

pub fn set_dhcp() -> Result<(String, Vec<(&'static str, String)>), Error> {
    let mut prompt = Prompt::new();
    let default_path = "/etc/netplan/00-installer-config.yaml";
    let config_path =
        prompt.readline_with_default(&format!("config path [{}]: ", default_path), default_path);

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

    log_step("Writing", &config_path);
    fs::write(&config_path, &yaml).map_err(|err| Error {
        error: vec![Box::new(err)],
        error_on: "set_dhcp",
        error_while: "write yaml to config file",
    })?;

    log_step("Applying", "netplan");
    execute_command(&mut vec!["netplan", "apply"], "set_dhcp", "apply netplan")?;

    log_step("Releasing", "current lease");
    execute_command(&mut vec!["dhclient", "-r"], "set_dhcp", "clear connection")?;

    log_step("Requesting", "new DHCP lease");
    execute_command(&mut vec!["dhclient"], "set_dhcp", "get dhcp client")?;

    let new_ip = local_ip().map(|ip| ip.to_string()).unwrap_or_else(|_| {
        log_warn("could not determine new IP");
        "unknown".to_string()
    });

    Ok((
        "Network set to DHCP".to_string(),
        vec![
            ("Config", config_path),
            ("IP", new_ip),
        ],
    ))
}
