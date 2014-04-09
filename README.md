osx-setup
=========

Install OS X
------------
1. Erase disk "Case-sensitive, Journaled" with "Disk Utility" (hold down `⌘+R` during startup to boot into *recovery* mode)
2. Follow instructions to install OS X
3. Encrypt disk ("System Preferences" > "Security & Privacy" > "FileVault")

Migrate data from your previous computer
----------------------------------------
- Copy `~/.ssh/`
- Copy `~/Projects/`

Install Xcode
-------------
Trigger the Xcode installer:
```
xcrun --version
```

Run the installer
-----------------
Base installation:
```bash
(cd $(mktemp -dti) && curl -O https://raw.github.com/cargomedia/osx-setup/master/install.sh && bash install.sh)
```

DNS server:
```bash
(cd $(mktemp -dti) && curl -O https://raw.github.com/cargomedia/osx-setup/master/install.sh && ROLE=dns bash install.sh)
```

Java
----
Java is not installed by default because of an issue with PhpStorm (won't boot) that prefers to
install its best JDK at first launch. Install Java manually if needed:
```bash
brew cask install java
```
