use std::vec;

use is_root::is_root;

use crate::{
    cmd::Prompt,
    log::{print_block, print_error},
};

mod error;
mod log;
mod network;
mod cmd;

fn main() {
    if !is_root() {
        println!("program must run with root permission");
        std::process::exit(1);
    }
    let mut prompt = Prompt::new();
    loop {
        println!("1. network");
        println!("q. quit");
        let input = prompt.readline("choice: ");

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
