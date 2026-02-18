use std::{fs, io::Write, path::Path};

use crate::{
    cmd::{Prompt, execute_command, is_valid_chmod},
    error::{Error, Result},
    file::open_with_append_or_create,
    log::{log_info, log_step, log_warn},
    samba::ShareConfig,
};

pub fn share() -> Result<(String, Vec<(&'static str, String)>)> {
    let mut prompt = Prompt::new();
    let config = ShareConfig::request(&mut prompt);
    let mut ask_permission = || {
        prompt.readline_with_default(&format!("{} permission access [777]: ", config.path), "777")
    };
    let mut permission = ask_permission();
    while !is_valid_chmod(&permission) {
        log_warn("invalid chmod permission");
        permission = ask_permission();
    }

    let path = Path::new(&config.path);
    if !path.is_dir() {
        if path.exists() {
            return Err(Error {
                error: vec![Box::new("path is not a valid directory")],
                error_on: "share",
                error_while: "check directory",
            });
        }
        log_step("Create", &config.path);
        fs::create_dir_all(&config.path).map_err(|err| Error {
            error: vec![Box::new(err)],
            error_on: "share",
            error_while: "create dir",
        })?;
    }

    log_step("Set", "permission");
    execute_command(
        &mut vec!["chmod", "-R", &permission, &config.path],
        "share",
        "set directory permission",
    )?;

    log_step("Apply", "config");
    let mut smb = open_with_append_or_create("/etc/samba/smb.conf");
    smb.write_all(format!("\n{}", config.to_string()).as_bytes())
        .map_err(|err| Error {
            error: vec![Box::new(err)],
            error_on: "share",
            error_while: "append config",
        })?;

    log_step("Restarting", "samba");
    execute_command(
        &mut vec!["systemctl", "restart", "smbd", "nmbd"],
        "share",
        "restart smbd and nmbd",
    )?;

    let smbpasswd_format = |users: &Vec<String>| {
        users
            .iter()
            .map(|user: _| format!("\t> sudo smbpasswd -a {user}"))
            .collect::<Vec<String>>()
            .join("\n")
    };
    log_info(&format!(
        "Don't forget to\n{}\n{}",
        smbpasswd_format(&config.valid_users),
        smbpasswd_format(&config.admin_users)
    ));

    Ok((
        "Shared folder with samba configured".to_string(),
        vec![
            ("name", config.name),
            ("path", config.path),
            ("read only", config.read_only.to_string()),
            ("browseable", config.browseable.to_string()),
            ("valid users", config.valid_users.join(", ")),
            ("admin users", config.admin_users.join(", ")),
        ],
    ))
}
