use std::vec;

use is_root::is_root;

use crate::{
    command::prompt,
    log::{print_block, print_error},
};

mod command;
mod error;
mod log;
mod network;

fn main() {
    if !is_root() {
        println!("program must run with root permission");
        std::process::exit(1);
    }
    loop {
        println!("1. network");
        println!("q. quit");
        let input = prompt("choice: ");

        match input.as_str() {
            "1" => {
                match network::run() {
                    Err(err) => print_error(err),
                    Ok(result) => print_block(vec![result]),
                };
            }
            "q" => break,
            _ => println!("invalid choice"),
        }
    }
}
