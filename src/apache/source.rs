use std::fs;

use crate::{
    cmd::{execute_command, execute_command_must_success},
    error::{self, Error},
};

pub fn delete_source() -> error::Result<()> {
    fs::remove_dir_all("/var/www/html").map_err(|err| Error {
        error: vec![Box::new(err)],
        error_on: "delete_source",
        error_while: "remove /var/www/html",
    })?;
    Ok(())
}

pub fn reset_source_permission() -> error::Result<()> {
    let output = execute_command(
        &["stat", "-c", "%U:%G", "/var/www"],
        "reset_source_permission",
        "check owner",
    )?;

    let owner = String::from_utf8_lossy(&output.stdout).trim().to_string();

    execute_command_must_success(
        &["chown", "-R", &owner, "/var/www/html"],
        "reset_source_permission",
        "reset ownership",
    )?;

    execute_command_must_success(
        &[
            "find",
            "/var/www/html",
            "-type",
            "d",
            "-exec",
            "chmod",
            "755",
            "{}",
            "+",
        ],
        "reset_source_permission",
        "reset dir permissions",
    )?;

    execute_command_must_success(
        &[
            "find",
            "/var/www/html",
            "-type",
            "f",
            "-exec",
            "chmod",
            "644",
            "{}",
            "+",
        ],
        "reset_source_permission",
        "reset file permissions",
    )?;

    Ok(())
}
