use colored::Colorize;

use crate::error::Error;

// ─── Symbols ─────────────────────────────────────────────────────────────────
const TICK: &str = "✓";
const CROSS: &str = "✗";
const INFO: &str = "○";
const ARROW: &str = "▲";
const WAIT: &str = "●";

// ─── Public API ──────────────────────────────────────────────────────────────

/// Print a single info/neutral log line.
pub fn log_info(msg: &str) {
    println!(" {} {}", INFO.cyan(), msg.white());
}

/// Print a success line (green tick, like Next.js "Ready").
pub fn log_success(msg: &str) {
    println!(" {} {}", TICK.green(), msg.bold().white());
}

/// Print a warning line (yellow triangle).
pub fn log_warn(msg: &str) {
    println!(" {} {}", ARROW.yellow(), msg.yellow());
}

/// Print an action that is currently running (label + detail).
pub fn log_step(label: &str, msg: &str) {
    let padded = format!("{:<12}", label);
    println!(
        " {} {} {}",
        WAIT.bright_cyan(),
        padded.cyan().bold(),
        msg.white()
    );
}

/// Print a key-value pair (like Next.js "- Local: http://localhost:3000").
pub fn log_kv(key: &str, value: &str) {
    let padded = format!("{:<14}", key);
    println!(
        "   {} {}  {}",
        "-".bright_black(),
        padded.white(),
        value.bright_white().bold()
    );
}

/// Print an error block, styled like Next.js error output.
pub fn log_error(err: &Error) {
    let border = "─".repeat(50).bright_black().to_string();

    eprintln!();
    eprintln!(
        " {} {}",
        CROSS.red().bold(),
        " Error ".on_red().white().bold()
    );
    eprintln!(" {}", border);
    eprintln!("   {} {}", "on:".bright_black(), err.error_on.white());
    eprintln!("   {} {}", "while:".bright_black(), err.error_while.white());
    eprintln!(" {}", border);
    for e in &err.error {
        eprintln!(
            "   {} {}",
            "│".red(),
            format!("{:?}", e.as_ref()).bright_white()
        );
    }
    eprintln!(" {}", border);
    eprintln!();
}

/// Print a titled result box (success summary).
pub fn log_result(title: &str, pairs: Vec<(&str, String)>) {
    let max_key_len = pairs.iter().map(|(k, _)| k.len()).max().unwrap_or(10);

    let max_val_len = pairs.iter().map(|(_, v)| v.len()).max().unwrap_or(20);

    let title_len = title.len() + 4; // +4 " ✓ "

    let content_width = max_key_len + 2 + max_val_len + 2;
    let total_width = content_width.max(title_len);

    let border_top = format!(" ┌{}┐", "─".repeat(total_width)).bright_black();
    let border_bottom = format!(" └{}┘", "─".repeat(total_width)).bright_black();
    let border_mid = format!(" ├{}┤", "─".repeat(total_width)).bright_black();

    println!();
    println!("{}", border_top);

    let title_text = format!(" {} {}", TICK.green(), title.bold());
    println!(
        " {} {:<width$} {}",
        "│".bright_black(),
        title_text,
        "│".bright_black(),
        width = total_width
    );

    println!("{}", border_mid);

    for (key, val) in pairs {
        println!(
            " {} {:<key_w$}  {:<val_w$} {}",
            "│".bright_black(),
            key.bright_black(),
            val.bright_white().bold(),
            "│".bright_black(),
            key_w = max_key_len,
            val_w = max_val_len
        );
    }

    println!("{}", border_bottom);
    println!();
}

/// Print the top-level menu header banner.
pub fn log_banner() {
    println!();
    println!("{}", "  ┌─────────────────────────────────────┐".bright_cyan());
    println!("{}", "  │          deb-utils  v0.0.5          │".bright_cyan());
    println!("{}", "  └─────────────────────────────────────┘".bright_cyan());
    println!();
    println!(
        "   {} {}",
        INFO.cyan(),
        "Debian Server Utilities".white()
    );
    println!();
}

/// Print a section divider with a label.
pub fn log_section(label: &str) {
    println!();
    println!(" {} {}", ARROW.bright_cyan().bold(), label.white().bold());
}

/// Print an invalid-choice warning.
pub fn log_invalid_choice() {
    log_warn("invalid choice, try again");
}
