use std::{
    fs::{File, OpenOptions},
    io::Read,
};

use crate::error::{self, Error};

pub fn open_with_append_or_create(path: &str) -> File {
    OpenOptions::new()
        .read(true)
        .append(true)
        .create(true)
        .open(path)
        .unwrap()
}

pub fn read_file(file: &mut File, while_do: &'static str) -> error::Result<String> {
    let mut content = String::new();
    file.read_to_string(&mut content).map_err(|err| Error {
        error: vec![Box::new(err)],
        error_on: "read_file",
        error_while: while_do,
    })?;
    Ok(content)
}
