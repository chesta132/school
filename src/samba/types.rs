use core::fmt;

use crate::cmd::Prompt;

pub struct ShareConfig {
    pub name: String,
    pub path: String,
    pub read_only: bool,
    pub browseable: bool,
    pub valid_users: Vec<String>,
    pub admin_users: Vec<String>,
}

impl ShareConfig {
    pub fn request(prompt: &mut Prompt) -> Self {
        let name = Prompt::required(
            || prompt.readline("share name: "),
            "a share name is required",
        );
        let path = Prompt::required(|| prompt.readline("path: "), "a path is required");
        let read_only = prompt.read_bool("read only (y/n) [n]: ", vec!["y"], vec!["n"], false);
        let browseable = prompt.read_bool("browseable (y/n) [y]: ", vec!["y"], vec!["n"], true);
        let valid_users = Prompt::read_multiple(
            |valid_users: &mut Vec<String>| {
                let user_str = valid_users.join(", ");
                prompt.readline(&format!(
                    "valid users [{}]: ",
                    if valid_users.is_empty() {
                        "none"
                    } else {
                        &user_str
                    }
                ))
            },
            1,
            "at least one valid user is required",
        );
        let admin_users = Prompt::read_multiple(
            |admin_users: &mut Vec<String>| {
                let user_str = admin_users.join(", ");
                prompt.readline(&format!(
                    "admin users [{}]: ",
                    if admin_users.is_empty() {
                        "none"
                    } else {
                        &user_str
                    }
                ))
            },
            1,
            "at least one admin user is required",
        );

        Self {
            admin_users,
            browseable,
            name,
            path,
            read_only,
            valid_users,
        }
    }
}

impl fmt::Display for ShareConfig {
    fn fmt(&self, f: &mut fmt::Formatter<'_>) -> fmt::Result {
        writeln!(f, "[{}]", self.name)?;
        writeln!(f, "path = {}", self.path)?;
        writeln!(
            f,
            "read only = {}",
            if self.read_only { "yes" } else { "no" }
        )?;
        writeln!(
            f,
            "browseable = {}",
            if self.browseable { "yes" } else { "no" }
        )?;
        writeln!(f, "valid users = {}", self.valid_users.join(" "))?;
        writeln!(f, "admin users = {}", self.admin_users.join(" "))?;
        Ok(())
    }
}
