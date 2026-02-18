use std::{net::IpAddr, str::FromStr};

use crate::{cmd::Prompt, log::log_warn};

pub fn is_valid_dns(s: &str) -> bool {
    IpAddr::from_str(s).is_ok()
}

pub fn collect_dns(prompt: &mut Prompt) -> Vec<String> {
    let mut dns_list: Vec<String> = Vec::new();

    loop {
        let label = if dns_list.is_empty() {
            "dns [none]: ".to_string()
        } else {
            format!("dns [{}]: ", dns_list.join(", "))
        };

        let input = prompt.readline(&label);
        if input.is_empty() {
            break;
        }
        if is_valid_dns(&input) {
            dns_list.push(input);
        } else {
            log_warn("invalid DNS address");
        }
    }

    dns_list
}
