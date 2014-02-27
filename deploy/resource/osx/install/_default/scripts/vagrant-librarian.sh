VAGRANT='vagrant'
VAGRANT_PLUGIN='vagrant-librarian-puppet'
VAGRANT_PLUGIN_VERSION='0.3.1'

if (which $VAGRANT); then
    if ! ($VAGRANT plugin list | grep -q "$VAGRANT_PLUGIN.*$VAGRANT_PLUGIN_VERSION"); then
        $VAGRANT plugin install $VAGRANT_PLUGIN --plugin-version $VAGRANT_PLUGIN_VERSION
    fi
fi
