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

Run the installer
-----------------
Base installation:
```sh
(cd $(mktemp -dti) && curl -OL https://raw.githubusercontent.com/cargomedia/osx-setup/master/install.sh && bash install.sh)
```

Software updates
----------------
Most software updates can be performed by running the installer again.
You can cleanup your system afterwards with:
```sh
brew cleanup
gem cleanup
```
Some software installed using [Homebrew Cask](https://caskroom.github.io/) must be updated separately. You can get a list of all installed casks with:
```sh
brew cask list
```
Updating a specific cask can be done with:
```sh
brew cask install --force caskname
```
To update all installed casks at once, simply run:
```sh
brew cask install --force $(brew cask list)
```

You might need to reinstall Vagrant plugins after performing major updates of Vagrant this way. To do so, uninstall them first with:
```sh
rm -rf ~/.vagrant.d/gems/ ~/.vagrant.d/plugins.json
```
Then run the installer again to update your Vagrant plugins.
