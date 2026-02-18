use std::{fs::{File, OpenOptions}, io::Read};

pub fn open_with_append_or_create(path: &str) -> File {
    OpenOptions::new()
        .read(true)
        .append(true)
        .open(path)
        .unwrap_or_else(|_| File::create(path).unwrap())
}

pub fn read_file(file: &mut File) -> String {
    let mut content = String::new();
    file.read_to_string(&mut content).unwrap();
    content
}
