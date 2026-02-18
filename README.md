# deb-utils

> Interactive CLI utility for configuring **network** and **DNS (BIND9)** on Debian-based servers.

```
  ┌─────────────────────────────────────────┐
  │           deb-utils  v0.0.2             │
  └─────────────────────────────────────────┘
  ○  Debian Server Utilities

  ▲ Main Menu
     1  network
     2  bind9
     q  quit

  ❯
```

---

## Features

- **Network configuration** — set interface to DHCP or Static IP via netplan
- **BIND9 setup** — auto-install, configure forward/reverse zones, and register domain
- **Colored output** — pretty, structured logs inspired by Next.js CLI
- **Interactive prompts** — readline-based input with history and default values

---

## Requirements

- Debian / Ubuntu server
- Rust `edition = "2024"` (rustup recommended)
- Must be run as **root**

---

## Installation

```bash
git clone https://github.com/your-username/deb-utils
cd deb-utils
cargo build --release
sudo ./target/release/deb-utils
```

---

## Usage

### Network → DHCP

```
▲  Network Configuration
   1  set DHCP
   2  set Static
   q  back

❯ 1
config path [/etc/netplan/00-installer-config.yaml]:
dns [none]: 8.8.8.8
dns [8.8.8.8]:

● Writing      /etc/netplan/00-installer-config.yaml
● Applying     netplan
● Releasing    current lease
● Requesting   new DHCP lease

 ┌────────────────────────────────────────────────┐
 │  ✓ Network set to DHCP                         │
 ├────────────────────────────────────────────────┤
 │ Config          /etc/netplan/00-installer...   │
 │ IP              192.168.1.25                   │
 └────────────────────────────────────────────────┘
```

### Network → Static IP

Prompts for:

- Config path (default: `/etc/netplan/00-installer-config.yaml`)
- Address(es) in CIDR format (e.g. `192.168.1.10/24`)
- Gateway (e.g. `192.168.1.1`)
- DNS server(s) (e.g. `8.8.8.8`)

### BIND9 DNS

```
❯ 2
● Installing   bind9
ip [192.168.1.10]:
domain: example.sch.id

● Writing      reverse zone
● Writing      forward zone
● Registering  named.conf.local
● Restarting   bind9 service

 ┌────────────────────────────────────────────────┐
 │  ✓ DNS Registered                              │
 ├────────────────────────────────────────────────┤
 │ Domain          example.sch.id                 │
 │ IP              192.168.1.10                   │
 └────────────────────────────────────────────────┘
```

Automatically:

- Installs `bind9` via `apt`
- Writes `/etc/bind/db.<domain>` (forward zone)
- Writes `/etc/bind/db.<ip-base>` (reverse zone)
- Appends zone entries to `/etc/bind/named.conf.local`
- Updates `/etc/resolv.conf` with the new nameserver
- Restarts `bind9` service

---

## Project Structure

```
src/
├── main.rs            # entry point, main menu
├── log.rs             # colored logging utilities
├── file.rs            # file open/read helpers
├── error/
│   └── error_type.rs  # shared Error struct
├── cmd/
│   ├── prompt.rs      # rustyline wrapper
│   └── command.rs     # shell command executor
├── network/
│   ├── mod.rs         # network submenu
│   ├── types.rs       # netplan serde structs
│   ├── dhcp.rs        # DHCP configuration
│   ├── statics.rs     # static IP configuration
│   ├── address.rs     # IP/CIDR input & validation
│   └── dns.rs         # DNS input & validation
└── bind/
    ├── mod.rs          # bind9 setup flow
    ├── forward.rs      # forward zone writer
    ├── reverse.rs      # reverse zone writer
    ├── register.rs     # named.conf.local + resolv.conf
    └── templates/
        ├── db.forward  # forward zone template
        ├── db.reverse  # reverse zone template
        └── named.zone  # named.conf zone block template
```

---

## Dependencies

| Crate                  | Purpose                       |
| ---------------------- | ----------------------------- |
| `colored`              | Terminal color output         |
| `rustyline`            | Readline prompts with history |
| `local-ip-address`     | Detect current machine IP     |
| `serde` + `serde_yaml` | Netplan YAML serialization    |
| `is-root`              | Root permission check         |
| `regex`                | Domain validation             |

---

## License

Free to use for educational purposes.
