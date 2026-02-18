use std::process::{Command, Output};

use crate::error::{self, Error};

pub fn execute_command<'a>(
    commands: &[&'a str],
    on: &'static str,
    while_do: &'static str,
) -> Result<Output, Error> {
    if commands.is_empty() {
        return Err(Error {
            error_on: on,
            error_while: while_do,
            error: vec![Box::new("no command to execute")],
        });
    }

    let mut commands = commands.to_vec();
    let mut cmd = Command::new(commands[0]);
    commands.remove(0);
    cmd.args(commands.as_slice());

    cmd.output().map_err(|err| Error {
        error: vec![Box::new(err)],
        error_on: on,
        error_while: while_do,
    })
}

fn is_pkg_installed(pkg: &str, while_do: &'static str) -> error::Result<bool> {
    let check = execute_command(&vec!["dpkg", "-s", pkg], "is_pkg_installed", while_do)?;
    Ok(check.status.success())
}

fn filter_uninstalled_pkg<'a>(
    pkgs: &[&'a str],
    while_do: &'static str,
) -> error::Result<Vec<&'a str>> {
    let mut uninstalled = vec![];
    for pkg in pkgs {
        let is_installed = is_pkg_installed(pkg, while_do)?;
        if !is_installed {
            uninstalled.push(*pkg);
        }
    }
    Ok(uninstalled)
}

pub fn install_pkg(
    pkgs: &[&str],
    on: &'static str,
    while_do: &'static str,
    mut on_install: impl FnMut(),
) -> error::Result<bool> {
    let pkgs = filter_uninstalled_pkg(pkgs, while_do)?;
    if pkgs.is_empty() {
        return Ok(false);
    }
    let install = [vec!["apt", "install"], pkgs, vec!["-y"]].concat();

    on_install();
    execute_command(&install, on, while_do)?;
    Ok(true)
}

pub fn execute_command_must_success<'a>(
    commands: &[&'a str],
    on: &'static str,
    while_do: &'static str,
) -> Result<Output, Error> {
    let output = execute_command(commands, on, while_do)?;
    if !output.status.success() {
        return Err(Error {
            error: vec![
                Box::new("command execute not success"),
                Box::new(String::from_utf8(output.stderr)),
            ],
            error_on: on,
            error_while: while_do,
        });
    }
    Ok(output)
}

pub fn is_valid_chmod(s: &str) -> bool {
    s.len() == 3 && s.chars().all(|c| c.is_ascii_digit() && c <= '7')
}
