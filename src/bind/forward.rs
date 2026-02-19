use std::fs;

use crate::error::Error;

const DB_FORWARD: &str = include_str!("./templates/db.forward");

pub fn forward(ip: &String, domain: &String) -> Result<bool, Error> {
    let path = format!("/etc/bind/db.{}", domain);

    // Check if forward zone already exists with same content
    if let Ok(existing) = fs::read_to_string(&path) {
        let expected = DB_FORWARD.replace("$domain", domain).replace("$ip", ip);
        if existing.trim() == expected.trim() {
            return Ok(false); // Already exists, skip
        }
    }

    // Create or overwrite forward zone file
    let forward = DB_FORWARD.replace("$domain", domain).replace("$ip", ip);
    fs::write(&path, forward).map_err(|err| Error {
        error: vec![Box::new(err)],
        error_on: "forward",
        error_while: "write forwarded",
    })?;

    Ok(true) // New file created
}
