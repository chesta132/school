use rustyline::{DefaultEditor, error::ReadlineError};

use crate::log::log_warn;

pub struct Prompt {
    editor: DefaultEditor,
}

impl Prompt {
    pub fn new() -> Self {
        Self {
            editor: DefaultEditor::new().unwrap(),
        }
    }

    pub fn required(mut f: impl FnMut() -> String, msg: &str) -> String {
        let mut result = f();
        while result.is_empty() {
            log_warn(msg);
            result = f();
        }
        result
    }

    pub fn read_multiple(
        mut f: impl FnMut(&mut Vec<String>) -> String,
        min: usize,
        min_err: &str,
    ) -> Vec<String> {
        let mut bowl = vec![];
        loop {
            let result = f(&mut bowl);
            if result.is_empty() {
                if bowl.len() >= min {
                    return bowl;
                } else {
                    log_warn(min_err);
                    continue;
                }
            }
            bowl.push(result);
        }
    }

    pub fn readline(&mut self, label: &str) -> String {
        match self.editor.readline(label) {
            Ok(line) => {
                let trimmed = line.trim().to_string();
                let _ = self.editor.add_history_entry(&trimmed);
                trimmed
            }
            Err(ReadlineError::Interrupted) | Err(ReadlineError::Eof) => std::process::exit(0),
            Err(e) => panic!("{e}"),
        }
    }

    pub fn readline_with_default(&mut self, label: &str, default: &str) -> String {
        let result = self.readline(label);
        if result.is_empty() {
            default.to_string()
        } else {
            result
        }
    }

    pub fn read_bool(
        &mut self,
        label: &str,
        true_on: Vec<&str>,
        false_on: Vec<&str>,
        default: bool,
    ) -> bool {
        let input = self.readline(label);
        let input = input.trim().to_lowercase();

        if true_on.contains(&input.as_str()) {
            true
        } else if false_on.contains(&input.as_str()) {
            false
        } else {
            default
        }
    }
}
