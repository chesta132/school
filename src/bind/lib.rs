use std::fs;

use regex::Regex;

use crate::{cmd::Prompt, error::Error};

pub const GW_CONF_PATH: &str = "/etc/bind/named.conf";
pub const DEFAULT_CONF_PATH: &str = "/etc/bind/named.conf.local";

pub fn is_valid_domain(domain: &str) -> bool {
    let re =
        Regex::new(r"^(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,}$").unwrap();
    re.is_match(domain)
}

pub fn ask_conf_path(prompt: &mut Prompt) -> String {
    prompt.readline_with_default(
        &format!("register zone config path [{}]: ", DEFAULT_CONF_PATH),
        DEFAULT_CONF_PATH,
    )
}

/// Check all included config files in named.conf for existing zones
pub fn get_all_zone_configs() -> Result<Vec<String>, Error> {
    let gw_content = fs::read_to_string(GW_CONF_PATH).map_err(|err| Error {
        error: vec![Box::new(err)],
        error_on: "register",
        error_while: "read /etc/bind/named.conf",
    })?;

    let mut configs = vec![gw_content];

    // Find all include statements
    for line in configs[0].clone().lines() {
        if line.trim().starts_with("include") {
            if let Some(path) = line.split('"').nth(1) {
                if let Ok(content) = fs::read_to_string(path) {
                    configs.push(content);
                }
            }
        }
    }

    Ok(configs)
}
