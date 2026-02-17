use rustyline::{DefaultEditor, error::ReadlineError};

pub struct Prompt {
    editor: DefaultEditor,
}

impl Prompt {
    pub fn new() -> Self {
        Self {
            editor: DefaultEditor::new().unwrap(),
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

    pub fn readline_with_default(&mut self, label: &str, default: String) -> String {
        let result = self.readline(label);
        if result.is_empty() { default } else { result }
    }
}
