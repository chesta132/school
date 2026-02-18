use std::fs;

use crate::error::Error;
const DB_FORWARD: &str = include_str!("./templates/db.forward");

pub fn forward(ip: &String, domain: &String) -> Result<(), Error> {
    let forward = DB_FORWARD.replace("$domain", domain).replace("$ip", ip);

    fs::write(format!("/etc/bind/db.{}", domain), forward).map_err(|err| Error {
        error: vec![Box::new(err)],
        error_on: "forward",
        error_while: "write forwarded",
    })?;

    Ok(())
}
