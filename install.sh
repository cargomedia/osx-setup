#!/bin/bash
set -ex

if (test -z "${ROLE}"); then ROLE='default'; fi
cd $(mktemp -dt osx-setup)
git clone https://github.com/cargomedia/osx-setup.git .

# Increase sudo timeout
if ! (sudo grep -q 'timestamp_timeout' /etc/sudoers); then
	sudo cp /etc/sudoers /tmp/sudoers
	sudo bash -c 'echo -e "Defaults\ttimestamp_timeout = 240" >> /tmp/sudoers'
	sudo cp /tmp/sudoers /etc/sudoers
	sudo rm /tmp/sudoers
fi

# Run the installer
php deploy/dev.php ${ROLE} Sys_Install
