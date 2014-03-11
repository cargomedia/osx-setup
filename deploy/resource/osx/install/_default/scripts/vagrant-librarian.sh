VAGRANT='vagrant'
VAGRANT_PLUGIN='vagrant-librarian-puppet'

if (which $VAGRANT); then
    if ! ($VAGRANT plugin list | grep -q $VAGRANT_PLUGIN); then
        $VAGRANT plugin install $VAGRANT_PLUGIN
    fi
fi
