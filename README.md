# deb-utils

> Interactive CLI utility for configuring **network**, **DNS (BIND9)**, **Samba**, and **Apache2** on Debian-based servers.

```
  ┌─────────────────────────────────────────┐
  │           deb-utils  v0.0.5             │
  └─────────────────────────────────────────┘
  ○  Debian Server Utilities

  ▲ Main Menu
     1  network
     2  bind9
     3  samba
     4  apache2
     q  quit

  ❯
```

---

## Features

- **Network configuration** — set interface to DHCP or Static IP via netplan
- **BIND9 setup** — auto-install, configure forward/reverse zones with smart duplicate detection
- **Samba file sharing** — auto-install, configure shared folders with user access control
- **Apache2 web server** — auto-install, import GitHub repository as website source
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
# Built version
wget https://github.com/chesta132/school/releases/download/duv0.0.5/deb-utils
sudo chmod +x deb-utils
sudo ./deb-utils
```

```bash
# Self-build version
git clone -b deb-utils https://github.com/chesta132/school deb-utils
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
register config path [/etc/bind/named.conf.local]:
● Restarting   bind9 service

 ┌────────────────────────────────────────────────┐
 │  ✓ DNS Registered                              │
 ├────────────────────────────────────────────────┤
 │ Domain          example.sch.id                 │
 │ IP              192.168.1.10                   │
 │ Zone path       /etc/bind/named.conf.local     │
 └────────────────────────────────────────────────┘
```

**Smart Duplicate Detection:**

The BIND9 module now intelligently handles existing configurations:

- **Scans all included configs** — checks `/etc/bind/named.conf` and all included files for existing zones
- **Append-only mode** — adds only missing forward/reverse zones, never rewrites entire config
- **Skip duplicate PTR records** — prevents adding duplicate entries in reverse zone files
- **Detects existing domains** — if domain/IP already registered, skips zone creation entirely

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
 │  ✓ DNS Already Registered                      │
 ├────────────────────────────────────────────────┤
 │ Domain          example.sch.id                 │
 │ IP              192.168.1.10                   │
 └────────────────────────────────────────────────┘
```

This means you can safely re-run BIND9 setup without breaking existing configurations or creating conflicts.

**What it does automatically:**

- Installs `bind9` via `apt`
- Writes `/etc/bind/db.<domain>` (forward zone) — skips if content matches
- Writes `/etc/bind/db.<ip-base>` (reverse zone) — appends PTR records only
- Appends zone entries to `/etc/bind/named.conf.local` (or custom path) — adds only missing zones
- Scans all included config files to prevent duplicates across multiple files
- Updates `/etc/resolv.conf` with the new nameserver — skips if already present
- Restarts `bind9` service

### Samba File Sharing

```
❯ 3
● Installing   samba

▲  Samba Configuration
   1  share
   q  back

❯ 1
share name: data
path: /srv/data
read only (y/n) [n]: n
browseable (y/n) [y]: y
valid users [none]: chesta
valid users [chesta]:
admin users [none]: chesta
admin users [chesta]:
/srv/data permission access [777]: 770

● Create        /srv/data
● Set           permission
● Apply         config
● Restarting    samba

○ Don't forget to
    > sudo smbpasswd -a chesta

 ┌────────────────────────────────────────────────┐
 │  ✓ Shared folder with samba configured         │
 ├────────────────────────────────────────────────┤
 │ name             data                          │
 │ path             /srv/data                     │
 │ read only        false                         │
 │ browseable       true                          │
 │ valid users      chesta                         │
 │ admin users      chesta                         │
 └────────────────────────────────────────────────┘
```

Automatically:

- Installs `samba` via `apt`
- Creates the target directory if it doesn't exist
- Sets directory permission via `chmod -R`
- Appends share block to `/etc/samba/smb.conf`
- Restarts `smbd` and `nmbd` services
- Reminds you to run `smbpasswd -a` for each user

> **Note:** After setup, register each user's Samba password manually:
>
> ```bash
> sudo smbpasswd -a <username>
> ```

### Apache2 Web Server

```
❯ 4
● Installing   apache2

▲  Apache2 Configuration
   1  import github repository
   q  back

❯ 1
● Installing   git
github username: chesta132
github repository: school
repository branch [none]: pos-php
▲ this action deletes your /var/www/html directory and all files inside
are you sure to continue (y/n) [n]: y

● Remove       /var/www/html
● Import       school
● Set          permission

 ┌────────────────────────────────────────────────┐
 │  ✓ Successfully import school                  │
 ├────────────────────────────────────────────────┤
 │ username         chesta132                     │
 │ repository       school                        │
 │ branch           pos-php                       │
 └────────────────────────────────────────────────┘
```

Automatically:

- Installs `apache2` and `git` via `apt`
- Prompts for GitHub username, repository, and optional branch
- Removes existing `/var/www/html` directory (with confirmation)
- Clones the GitHub repository to `/var/www/html`
- Sets proper ownership and permissions:
  - Directories: `755`
  - Files: `644`
  - Owner: matches `/var/www` owner (typically `www-data`)

> **Note:** The repository should contain web files (HTML, CSS, JS, PHP, etc.) that will be served by Apache2. After import, your website will be immediately accessible via the server's IP address or configured domain.

---

## Project Structure

```
src/
├── main.rs            # entry point, main menu
├── log.rs             # colored logging utilities
├── file.rs            # file open/read helpers
├── error/
│   ├── mod.rs         # error submenu
│   └── types.rs       # shared Error struct
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
├── bind/
│   ├── mod.rs         # bind9 setup flow
│   ├── lib.rs         # shared helpers & zone config scanner
│   ├── forward.rs     # forward zone writer with duplicate detection
│   ├── reverse.rs     # reverse zone writer with PTR append logic
│   ├── register.rs    # named.conf zone registration with smart scanning
│   └── templates/
│       ├── db.forward  # forward zone template
│       ├── db.reverse  # reverse zone template
│       └── named.zone  # named.conf zone block template
├── samba/
│   ├── mod.rs         # samba submenu & install flow
│   ├── share.rs       # shared folder configuration
│   └── types.rs       # ShareConfig struct & smb.conf serialization
└── apache/
    ├── mod.rs         # apache2 submenu & install flow
    ├── git.rs         # GitHub repository import
    └── source.rs      # /var/www/html management & permission helpers
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
