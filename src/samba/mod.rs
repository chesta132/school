mod share;
mod types;

pub use types::*;

use crate::{cmd::install_pkg, error, log::log_step};

pub fn run() -> error::Result<(String, Vec<(&'static str, String)>)> {
    install_pkg(&vec!["samba"], "bind9", "install samba", || {
        log_step("Installing", "samba")
    })?;

    share::share()
}
