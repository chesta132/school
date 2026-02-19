use local_ip_address::local_ip;
use regex::Regex;

use crate::{
    cmd::{Prompt, execute_command, install_pkg},
    error::Error,
    log::{log_step, log_warn},
    network,
};

mod forward;
mod register;
mod reverse;

fn is_valid_domain(domain: &str) -> bool {
    let re =
        Regex::new(r"^(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,}$").unwrap();
    re.is_match(domain)
}

pub fn run() -> Result<(String, Vec<(&'static str, String)>), Error> {
    install_pkg(&vec!["bind9"], "bind9", "install bind9", || {
        log_step("Installing", "bind9")
    })?;

    let mut prompt = Prompt::new();
    let self_ip = local_ip().unwrap().to_string();

    let mut ip = prompt.readline_with_default(&format!("ip [{}]: ", &self_ip), &self_ip);
    while !network::address::is_valid_ip(&ip) {
        log_warn("invalid IP address");
        ip = prompt.readline_with_default(&format!("ip [{}]: ", &self_ip), &self_ip);
    }

    let mut domain = prompt.readline("domain [none]: ");
    while !is_valid_domain(&domain) {
        log_warn("invalid domain (e.g. example.com, sub.example.com, example.sch.id)");
        domain = prompt.readline("domain [none]: ");
    }

    log_step("Writing", "reverse zone");
    reverse::reverse(&ip, &domain)?;

    log_step("Writing", "forward zone");
    forward::forward(&ip, &domain)?;

    log_step("Registering", "named.conf.local");
    let register_zone = register::register(&ip, &domain)?;

    log_step("Restarting", "bind9 service");
    execute_command(
        &mut vec!["systemctl", "restart", "bind9"],
        "bind9",
        "restart bind service",
    )?;

    Ok((
        "DNS Registered".to_string(),
        vec![
            ("Domain", domain),
            ("IP", ip),
            ("Registered zone path", register_zone),
        ],
    ))
}
