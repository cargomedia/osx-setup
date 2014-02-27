#!/bin/bash
set -e

# Sudo timeout
if ! (sudo grep -q 'timestamp_timeout' /etc/sudoers); then
	sudo cp /etc/sudoers /tmp/sudoers
	sudo bash -c 'echo -e "Defaults\ttimestamp_timeout = 240" >> /tmp/sudoers'
	sudo cp /tmp/sudoers /etc/sudoers
	sudo rm /tmp/sudoers
fi
