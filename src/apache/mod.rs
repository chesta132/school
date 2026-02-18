use crate::{
    cmd::{Prompt, install_pkg},
    error,
    log::{log_invalid_choice, log_section, log_step},
};

pub mod git;
pub mod source;

pub fn run() -> error::Result<(String, Vec<(&'static str, String)>)> {
    let mut prompt = Prompt::new();
    install_pkg(&vec!["apache2"], "apache2", "install apache2", || log_step("Installing", "apache2"))?;

    loop {
        log_section("Apache2 Configuration");
        println!("   1  import github repository");
        println!("   q  back");
        println!();

        let input = prompt.readline("❯ ");

        match input.as_str() {
            "1" => return git::import_git(),
            "q" | "Q" => return Ok(("Cancelled".to_string(), vec![])),
            _ => log_invalid_choice(),
        }
    }
}
