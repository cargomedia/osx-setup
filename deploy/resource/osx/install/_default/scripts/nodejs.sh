NPM='/usr/local/bin/npm'

function npmInstall {
	if ! (${NPM} list -g "${1}" | grep -q "${1}@${2}"); then
		${NPM} install -g "${1}@${2}"
	fi
}

npmInstall browser-sync 2.8.0
npmInstall less 2.5.1
npmInstall bower 1.4.1
