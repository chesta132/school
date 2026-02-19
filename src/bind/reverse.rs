use std::fs;

use crate::error::Error;

const DB_REVERSE: &str = include_str!("./templates/db.reverse");

pub fn reverse(ip: &String, domain: &String) -> Result<(), Error> {
    let mut ip_parts = ip.split('.').collect::<Vec<&str>>();
    let lo = ip_parts.last().unwrap().to_owned();
    ip_parts.pop();
    let ip_base = ip_parts.join(".");

    let path = format!("/etc/bind/db.{}", ip_base);
    let new_ptr = format!("{}\tIN\tPTR\t{}.", lo, domain);

    let content = if let Ok(existing) = fs::read_to_string(&path) {
        // Check if PTR record already exists
        if existing.contains(&new_ptr) {
            return Ok(());
        }
        // Append new PTR record to existing file
        format!("{}\n{}", existing.trim_end(), new_ptr)
    } else {
        // Create new reverse zone file from template
        DB_REVERSE.replace("$domain", domain).replace("$lo", &lo)
    };

    fs::write(&path, content).map_err(|err| Error {
        error: vec![Box::new(err)],
        error_on: "reverse",
        error_while: "write reversed",
    })?;

    Ok(())
}
