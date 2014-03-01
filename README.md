osx-setup
=========

Install OS X
------------
1. Erase disk "Case-sensitive, Journaled" with "Disk Utility"
2. Install OSX
3. Encrypt disk (FileVault)

Migrate your data
-----------------
- Copy `~/.ssh/`
- Copy `~/Projects/`

Run the installer
-----------------
Base installation:
```bash
curl -Ls https://raw.github.com/cargomedia/osx-setup/master/osx-install.sh | bash
```

DNS server:
```bash
(export ROLE=DNS && curl -Ls https://raw.github.com/cargomedia/osx-setup/master/osx-install.sh | bash)
```
