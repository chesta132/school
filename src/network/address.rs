use std::{net::IpAddr, str::FromStr};

use crate::command::prompt;

pub fn is_valid_ip(s: &str) -> bool {
    IpAddr::from_str(s).is_ok()
}

pub fn collect_addresses() -> Vec<String> {
    let mut list = Vec::new();

    loop {
        let label = if list.is_empty() {
            "address [none]: ".to_string()
        } else {
            format!("address [{}]: ", list.join(", "))
        };

        let input = prompt(&label);

        if input.is_empty() {
            break;
        }

        let parts: Vec<&str> = input.split('/').collect();

        match parts.as_slice() {
            [ip, cidr] => {
                let valid_cidr = cidr
                    .parse::<u8>()
                    .map(|n| (1..=32).contains(&n))
                    .unwrap_or(false);

                if is_valid_ip(ip) && valid_cidr {
                    list.push(input);
                } else {
                    println!("invalid address, use CIDR format (example: 192.168.1.10/24)");
                }
            }
            _ => println!("invalid address, use CIDR format (example: 192.168.1.10/24)"),
        }
    }

    list
}

pub fn collect_gateway() -> Option<String> {
    loop {
        let input = prompt("gateway [none]: ");

        if input.is_empty() {
            return None;
        }

        if is_valid_ip(&input) {
            return Some(input);
        } else {
            println!("invalid gateway address");
        }
    }
}
