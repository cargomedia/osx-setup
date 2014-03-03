osx-setup
=========

Install OS X
------------
1. Erase disk "Case-sensitive, Journaled" with "Disk Utility"
2. Install OSX
3. Encrypt disk (FileVault)

Migrate data from your previous computer
----------------------------------------
- Copy `~/.ssh/`
- Copy `~/Projects/`

Run the installer
-----------------
Base installation:
```bash
curl -Ls https://raw.github.com/cargomedia/osx-setup/master/install.sh | bash
```

DNS server:
```bash
curl -Ls https://raw.github.com/cargomedia/osx-setup/master/install.sh | ROLE=dns bash
```
