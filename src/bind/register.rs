use std::{fs, io::Write};

use crate::{
    bind::lib::*,
    cmd::Prompt,
    error::Error,
    file::{open_with_append_or_create, read_file},
    log::{log_step, log_warn},
};

const NAMED_ZONE: &str = include_str!("./templates/named.zone");

pub fn register(ip: &String, domain: &String) -> Result<(String, bool), Error> {
    let mut prompt = Prompt::new();
    let mut reversed_ip = ip.split('.').collect::<Vec<&str>>();
    reversed_ip.pop();
    reversed_ip.reverse();

    let mut base_ip = ip.split(".").collect::<Vec<&str>>();
    base_ip.pop();

    let zone = NAMED_ZONE
        .replace("$domain", domain)
        .replace("$base_ip", &base_ip.join("."))
        .replace("$reversed_ip", &reversed_ip.join("."))
        .split("===")
        .map(str::to_owned)
        .collect::<Vec<String>>();

    // Check all included configs for existing zones
    let all_configs = get_all_zone_configs()?.join("\n");
    let forward_zone = zone[0].trim();
    let reverse_zone = zone[1].trim();

    let has_forward = all_configs.contains(forward_zone);
    let has_reverse = all_configs.contains(reverse_zone);

    // If both zones exist, skip registration
    if has_forward && has_reverse {
        return Ok((DEFAULT_CONF_PATH.to_string(), false));
    }

    let mut conf_path = ask_conf_path(&mut prompt);

    let (mut conf, existing) = loop {
        let Ok(mut conf) = open_with_append_or_create(&conf_path, "open zone config path") else {
            log_warn(&format!("can not open {}", conf_path));
            conf_path = ask_conf_path(&mut prompt);
            continue;
        };

        if let Ok(content) = read_file(&mut conf, "read zone config path") {
            break (conf, content);
        } else {
            log_warn(&format!("can not read {}", conf_path));
            conf_path = ask_conf_path(&mut prompt);
        }
    };

    // Append only missing zones
    let mut zones_to_add = Vec::new();
    if !has_forward && !existing.contains(forward_zone) {
        zones_to_add.push(forward_zone);
    }
    if !has_reverse && !existing.contains(reverse_zone) {
        zones_to_add.push(reverse_zone);
    }

    if !zones_to_add.is_empty() {
        let zone_content = zones_to_add.join("\n\n");
        conf.write_all(format!("\n{}\n", zone_content).as_bytes())
            .map_err(|err| Error {
                error: vec![Box::new(err)],
                error_on: "register",
                error_while: "write and register zone",
            })?;
    }

    // Include config file in main named.conf if needed
    if conf_path != DEFAULT_CONF_PATH {
        if prompt.read_bool(
            &format!("include {} to {} (y/n) [y]", conf_path, GW_CONF_PATH),
            vec!["y"],
            vec!["n"],
            true,
        ) {
            log_step("Including", &format!("{} to {}", conf_path, GW_CONF_PATH));
            let include = format!("include \"{}\";", conf_path);
            let gw_content = fs::read_to_string(GW_CONF_PATH).map_err(|err| Error {
                error: vec![Box::new(err)],
                error_on: "register",
                error_while: "read /etc/bind/named.conf",
            })?;

            if !gw_content.contains(&include) {
                fs::write(
                    GW_CONF_PATH,
                    format!("{}\n{}", include, gw_content).as_bytes(),
                )
                .map_err(|err| Error {
                    error: vec![Box::new(err)],
                    error_on: "register",
                    error_while: "write /etc/bind/named.conf",
                })?;
            }
        }
    }

    // Add nameserver to resolv.conf if not exists
    let resolv_content = fs::read_to_string("/etc/resolv.conf").map_err(|err| Error {
        error: vec![Box::new(err)],
        error_on: "register",
        error_while: "read /etc/resolv.conf",
    })?;
    let new_nameserver = format!("nameserver {ip}");

    if !resolv_content.contains(&new_nameserver) {
        fs::write(
            "/etc/resolv.conf",
            format!("{}\n{}", new_nameserver, resolv_content),
        )
        .map_err(|err| Error {
            error: vec![Box::new(err)],
            error_on: "register",
            error_while: "write resolv.conf",
        })?;
    }

    Ok((conf_path, true))
}
