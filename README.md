osx-setup
=========
Code and docu to set up and configure a *Cargo Media*-flavored OS X.

Following the instructions below you will end up with:
- Homebrew and some [utility packages](/deploy/resource/osx/install/_default/brew.list).
- Homebrew-cask to install [useful GUI apps](/deploy/resource/osx/install/_default/brew-cask.list).
- PHP 5.4, composer.
- Ruby, rbenv and some [utility gems](/deploy/resource/osx/install/_default/scripts/ruby.sh).
- Puppet, vagrant and [some vagrant plugins](/deploy/resource/osx/install/_default/scripts/vagrant-plugins.sh).

Install OS X
------------
1. Erase disk "Case-sensitive, Journaled" with "Disk Utility" (hold down `⌘+R` during startup to boot into *recovery* mode)
2. Follow instructions to install OS X
3. Encrypt disk ("System Preferences" > "Security & Privacy" > "FileVault")

Migrate data from your previous computer
----------------------------------------
- Copy `~/.ssh/`
- Copy `~/Projects/`

Install Xcode CLI tools
-----------------------
Install the *Command Line Developer Tools*:
```
xcode-select --install
```

Run the installer
-----------------
Base installation:
```sh
(cd $(mktemp -dti) && curl -OL https://raw.githubusercontent.com/cargomedia/osx-setup/master/install.sh && bash install.sh)
```

Java
----
Java is not installed by default because of an issue with PhpStorm (won't boot) that prefers to
install its best JDK at first launch. Install Java manually if needed:
```sh
brew cask install java
```
