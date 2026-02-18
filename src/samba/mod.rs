mod share;
mod types;

pub use types::*;

use crate::{
    cmd::{Prompt, install_pkg},
    error,
    log::{log_invalid_choice, log_section, log_step},
};

pub fn run() -> error::Result<(String, Vec<(&'static str, String)>)> {
    let mut prompt = Prompt::new();
    install_pkg(&vec!["samba"], "bind9", "install samba", || {
        log_step("Installing", "samba")
    })?;

    loop {
        log_section("Samba Configuration");
        println!("   1  share");
        println!();

        let input = prompt.readline("❯ ");

        match input.as_str() {
            "1" => return share::share(),
            "q" | "Q" => return Ok(("Cancelled".to_string(), vec![])),
            _ => log_invalid_choice(),
        }
    }
}
