use std::{fs, io::Write};

use crate::{
    cmd::Prompt,
    error::Error,
    file::{open_with_append_or_create, read_file},
};

const NAMED_ZONE: &str = include_str!("./templates/named.zone");

pub fn register(ip: &String, domain: &String) -> Result<(), Error> {
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

    let conf_path = "/etc/bind/named.conf.local";
    let conf_path = Prompt::new().readline_with_default(
        &format!("register config path [{}]: ", conf_path),
        conf_path,
    );

    let mut conf = open_with_append_or_create(&conf_path);
    let existing = read_file(&mut conf);

    let zone_to_append = if existing.contains(zone[1].trim()) {
        &zone[0]
    } else {
        &zone.join("\n")
    };

    conf.write_all(("\n".to_string() + zone_to_append).as_bytes())
        .map_err(|err| Error {
            error: vec![Box::new(err)],
            error_on: "register",
            error_while: "write and register zone",
        })?;

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

    Ok(())
}
