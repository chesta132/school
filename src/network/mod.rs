use crate::{command::prompt, error::Error};

pub mod address;
pub mod dhcp;
pub mod dns;
pub mod statics;
pub mod types;
pub use crate::network::types::*;

pub fn run() -> Result<String, Error> {
    loop {
        println!("1. set dhcp");
        println!("2. set static");
        println!("q. quit");
        let input = prompt("choice: ");

        match input.as_str() {
            "1" => return dhcp::set_dhcp(),
            "2" => return statics::set_static(),
            "q" => return Ok("Quit".to_string()),
            _ => println!("invalid choice"),
        }
    }
}
