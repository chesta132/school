use crate::{cmd::Prompt, error::Error, log::{log_invalid_choice, log_section}};

pub mod address;
pub mod dhcp;
pub mod dns;
pub mod statics;
pub mod types;
pub use crate::network::types::*;

pub fn run() -> Result<(String, Vec<(&'static str, String)>), Error> {
    let mut prompt = Prompt::new();

    loop {
        log_section("Network Configuration");
        println!("   1  set DHCP");
        println!("   2  set Static");
        println!("   q  back");
        println!();

        let input = prompt.readline("❯ ");

        match input.as_str() {
            "1" => return dhcp::set_dhcp(),
            "2" => return statics::set_static(),
            "q" | "Q" => return Ok(("Cancelled".to_string(), vec![])),
            _ => log_invalid_choice(),
        }
    }
}
