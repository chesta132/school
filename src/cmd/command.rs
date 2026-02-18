use std::process::{Command, Output};

use crate::error::Error;

pub fn execute_command<'a>(
    commands: &mut Vec<&'a str>,
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

    let mut cmd = Command::new(commands[0]);
    commands.remove(0);
    cmd.args(commands.as_slice());

    cmd.output().map_err(|err| Error {
        error: vec![Box::new(err)],
        error_on: on,
        error_while: while_do,
    })
}

pub fn is_valid_chmod(s: &str) -> bool {
    s.len() == 3 && s.chars().all(|c| c.is_ascii_digit() && c <= '7')
}
