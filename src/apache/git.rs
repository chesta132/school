use std::path::Path;

use crate::{
    apache,
    cmd::{Prompt, execute_command_must_success, install_pkg},
    error,
    log::{log_step, log_warn},
};

pub fn import_git() -> error::Result<(String, Vec<(&'static str, String)>)> {
    install_pkg(&vec!["git"], "import_git", "install git", || {
        log_step("Installing", "git")
    })?;
    let mut prompt = Prompt::new();

    let username = Prompt::required(
        || prompt.readline("github username: "),
        "username is required",
    );
    let repository = Prompt::required(
        || prompt.readline("github repository: "),
        "repository is required",
    );
    let branch = prompt.readline("repository branch [none]: ");

    if Path::new("/var/www/html").exists() {
        log_warn("this action deletes your /var/www/html directory and all files inside");
        let next = prompt.read_bool(
            "are you sure to continue (y/n) [n]: ",
            vec!["y"],
            vec!["n"],
            false,
        );
        if !next {
            return Ok(("Cancelled".to_string(), vec![]));
        }

        log_step("Remove", "/var/www/html");
        apache::source::delete_source()?;
    }

    let url = format!("https://github.com/{}/{}", &username, &repository);
    let mut command = vec!["git", "clone"];
    if !branch.is_empty() {
        command.append(&mut vec!["-b", &branch]);
    }
    command.append(&mut vec![&url, "/var/www/html"]);

    log_step("Import", &repository);
    execute_command_must_success(&command, "import_git", "import repository")?;

    log_step("Set", "permission");
    apache::source::reset_source_permission()?;

    Ok((
        format!("Successfully import {}", repository),
        vec![
            ("username", username),
            ("repository", repository),
            (
                "branch",
                if branch.is_empty() {
                    "none".to_string()
                } else {
                    branch
                },
            ),
        ],
    ))
}
