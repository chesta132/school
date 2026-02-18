use is_root::is_root;

use crate::log::{log_banner, log_error, log_invalid_choice, log_result, log_section, log_warn};

mod bind;
mod cmd;
mod error;
mod file;
mod log;
mod network;

fn main() {
    log_banner();

    if !is_root() {
        log_warn("program must run with root permission");
        std::process::exit(1);
    }

    let mut prompt = cmd::Prompt::new();

    loop {
        log_section("Main Menu");
        println!("   1  network");
        println!("   2  bind9");
        println!("   q  quit");
        println!();

        let input = prompt.readline("❯ ");

        match input.as_str() {
            "1" => match network::run() {
                Err(err) => log_error(&err),
                Ok((title, pairs)) => log_result(&title, pairs),
            },
            "2" => match bind::run() {
                Err(err) => log_error(&err),
                Ok((title, pairs)) => log_result(&title, pairs),
            },
            "q" | "Q" => {
                println!();
                break;
            }
            _ => log_invalid_choice(),
        }
    }
}
