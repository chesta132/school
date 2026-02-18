use std::fs;

use crate::{
    cmd::{execute_command, Prompt},
    error::Error,
    log::{log_step, log_warn},
    network::{
        address::{collect_addresses, collect_gateway},
        dns::collect_dns,
        *,
    },
};

pub fn set_static() -> Result<(String, Vec<(&'static str, String)>), Error> {
    let mut prompt = Prompt::new();
    let default_path = "/etc/netplan/00-installer-config.yaml";
    let config_path =
        prompt.readline_with_default(&format!("config path [{}]: ", default_path), default_path);

    let mut addresses = collect_addresses();
    while addresses.is_empty() {
        log_warn("at least one address is required");
        addresses = collect_addresses();
    }

    let mut gateway = collect_gateway();
    while gateway.is_none() {
        log_warn("a gateway is required");
        gateway = collect_gateway();
    }

    let mut dns_list = collect_dns();
    while dns_list.is_empty() {
        log_warn("at least one DNS server is required");
        dns_list = collect_dns();
    }

    let nameservers = Some(NameServers {
        addresses: Some(dns_list.clone()),
    });

    let conf = Config {
        network: Network {
            ethernets: Ethernets {
                enp0s3: Enp0s {
                    dhcp4: false,
                    addresses: Some(addresses.clone()),
                    gateway4: gateway.clone(),
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

    log_step("Writing", &config_path);
    fs::write(&config_path, &yaml).map_err(|err| Error {
        error: vec![Box::new(err)],
        error_on: "set_static",
        error_while: "write yaml to config file",
    })?;

    log_step("Applying", "netplan");
    execute_command(&mut vec!["netplan", "apply"], "set_static", "apply netplan")?;

    Ok((
        "Network set to Static".to_string(),
        vec![
            ("Config",   config_path),
            ("Addresses", addresses.join(", ")),
            ("Gateway",  gateway.unwrap_or_default()),
            ("DNS",      dns_list.join(", ")),
        ],
    ))
}
