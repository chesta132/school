use std::fs;

use crate::{
    cmd::{Prompt, execute_command},
    error::Error,
    network::{
        address::{collect_addresses, collect_gateway},
        dns::collect_dns,
        *,
    },
};

pub fn set_static() -> Result<String, Error> {
    let mut prompt = Prompt::new();
    let default_path = "/etc/netplan/00-installer-config.yaml";
    let config_path = prompt.readline_with_default(
        &format!("config path [{}]: ", default_path),
        default_path.to_string(),
    );

    let mut addresses = collect_addresses();
    while addresses.is_empty() {
        println!("addresses must inserted at least one");
        addresses = collect_addresses();
    }

    let mut gateway = collect_gateway();
    while gateway == None {
        println!("gateway is needed");
        gateway = collect_gateway();
    }

    let mut dns_list = collect_dns();
    while dns_list.is_empty() {
        println!("dns must inserted at least one");
        dns_list = collect_dns();
    }

    let nameservers = Some(NameServers {
        addresses: Some(dns_list),
    });

    let conf = Config {
        network: Network {
            ethernets: Ethernets {
                enp0s3: Enp0s {
                    dhcp4: false,
                    addresses: Some(addresses),
                    gateway4: gateway,
                    nameservers,
                },
            },
            version: 2,
        },
    };

    let yaml = serde_yaml::to_string(&conf).map_err(|err| Error {
        error: vec![Box::new(err)],
        error_on: "set_static",
        error_while: "convert config to yaml",
    })?;

    fs::write(&config_path, &yaml).map_err(|err| Error {
        error: vec![Box::new(err)],
        error_on: "set_static",
        error_while: "write yaml to config file",
    })?;

    execute_command(&mut vec!["netplan", "apply"], "set_static", "apply netplan")?;

    Ok(format!(
        "Successfully set network to static\nConfig: {}",
        config_path
    ))
}
