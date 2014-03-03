GEM='/usr/local/bin/gem'

function gemInstall {
	if ! (${GEM} list --no-versions | grep -q $1); then
		${GEM} install $1
	fi
}

${GEM} update --system
${GEM} update --force

gemInstall bundler
gemInstall fontcustom
gemInstall capistrano
gemInstall pulsar
gemInstall puppet
