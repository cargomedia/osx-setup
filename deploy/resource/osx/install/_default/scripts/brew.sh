if ! (which -s brew); then
	ruby -e "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/master/install)"
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

if ! (brew tap | grep -q '^homebrew/versions'); then
	brew tap homebrew/versions > /dev/null
fi

if ! (brew tap | grep -q '^mcuadros/homebrew-hhvm'); then
	brew tap mcuadros/homebrew-hhvm > /dev/null
fi

if ! (brew tap | grep -q '^homebrew/php$'); then
	brew tap homebrew/php > /dev/null
fi

if ! (brew tap | grep -q '^homebrew/science'); then
	brew tap homebrew/science > /dev/null
fi

if ! (brew tap | grep -q '^cargomedia/cargomedia$'); then
	brew tap cargomedia/cargomedia > /dev/null
fi

if ! (brew tap | grep -q '^caskroom/cask$'); then
	brew tap caskroom/cask > /dev/null
fi
