use crate::{cmd::execute_command, error};

fn is_pkg_installed(pkg: &str, while_do: &'static str) -> error::Result<bool> {
    let check = execute_command(&vec!["dpkg", "-s", pkg], "is_pkg_installed", while_do)?;
    Ok(check.status.success())
}

pub fn is_valid_chmod(s: &str) -> bool {
    s.len() == 3 && s.chars().all(|c| c.is_ascii_digit() && c <= '7')
}

pub fn filter_uninstalled_pkg<'a>(
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
