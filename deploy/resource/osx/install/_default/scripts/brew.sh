if ! (which -s brew); then
	ruby -e "$(curl -fsSL https://raw.github.com/mxcl/homebrew/go/install)"
	brew update
fi

PATH='/usr/local/bin:/usr/bin:/bin:/usr/local/sbin:/usr/sbin:/sbin:/opt/X11/bin:/usr/X11/bin'
sudo sh -c "echo 'setenv PATH $PATH' > /etc/launchd.conf"
sudo launchctl setenv PATH $PATH
sudo sh -c 'echo "$(echo $PATH | tr ":" "\n")" > /etc/paths'

if ! (brew tap | grep -q '^homebrew/dupes$'); then
	brew tap homebrew/dupes > /dev/null
fi

if ! (brew tap | grep -q '^homebrew/binary'); then
	brew tap homebrew/binary > /dev/null
fi

if ! (brew tap | grep -q '^cargomedia/php$'); then
	brew tap cargomedia/php > /dev/null
fi

if ! (brew tap | grep -q '^cargomedia/cargomedia$'); then
	brew tap cargomedia/cargomedia > /dev/null
fi

if ! (brew tap | grep -q '^phinze/cask$'); then
	brew tap phinze/cask > /dev/null
fi
