use local_ip_address::local_ip;

use crate::{
    cmd::{Prompt, execute_command},
    error::Error,
    network,
};

mod forward;
mod register;
mod reverse;
use regex::Regex;

fn is_valid_domain(domain: &str) -> bool {
    let re =
        Regex::new(r"^(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,}$").unwrap();
    re.is_match(domain)
}

pub fn run() -> Result<String, Error> {
    execute_command(
        &mut vec!["apt", "install", "bind9", "-y"],
        "bind9",
        "install bind9",
    )?;

    let mut prompt = Prompt::new();
    let self_ip = local_ip().unwrap().to_string();

    let mut ip = prompt.readline_with_default(&format!("ip [{}]: ", &self_ip), &self_ip);
    while !network::address::is_valid_ip(&ip) {
        println!("invalid address");
        ip = prompt.readline_with_default(&format!("ip [{}]: ", &self_ip), &self_ip);
    }

    let mut domain = prompt.readline("domain: ");
    while !is_valid_domain(&domain) {
        println!("invalid domain (example: example.com, sub.example.com, example.sch.id)");
        domain = prompt.readline(&format!("domain: "));
    }

    reverse::reverse(&ip, &domain)?;
    forward::forward(&ip, &domain)?;
    register::register(&ip, &domain)?;

    execute_command(
        &mut vec!["systemctl", "restart", "bind9"],
        "bind9",
        "restart bind service",
    )?;

    Ok(format!(
        "Successfully register domain\nDomain: {}IP: {}",
        domain, ip
    ))
}
