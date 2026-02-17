use std::{
    io::{self, Write},
    process::{Command, Output},
};

use crate::error::Error;

pub fn execute_command<'a>(
    commands: &mut Vec<&'a str>,
    on: &'static str,
    while_do: &'static str,
) -> Result<Output, Error> {
    if commands.len() <= 0 {
        return Err(Error {
            error_on: on,
            error_while: while_do,
            error: vec![Box::new("no command to execute")],
        });
    }

    let mut cmd = Command::new(commands[0]);
    commands.remove(0);
    cmd.args(commands.as_slice());

    match cmd.output() {
        Err(err) => Err(Error {
            error: vec![Box::new(err)],
            error_on: on,
            error_while: while_do,
        }),
        Ok(ok) => Ok(ok),
    }
}

pub fn prompt(label: &str) -> String {
    print!("{}", label);
    io::stdout().flush().unwrap();
    let mut input = String::new();
    io::stdin().read_line(&mut input).unwrap();
    input.trim().to_string()
}

pub fn prompt_with_default(label: &str, default: String) -> String {
    let result = prompt(label);
    if result.is_empty() { default } else { result }
}
