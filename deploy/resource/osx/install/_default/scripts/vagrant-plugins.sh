VAGRANT='vagrant'

function installVagrantPlugin {
  if (which $VAGRANT >/dev/null); then
    if ! ($VAGRANT plugin list | grep -q $1); then
      $VAGRANT plugin install "${1}" --plugin-version "${2}"
    fi
  fi
}

installVagrantPlugin vagrant-librarian-puppet 0.7.1
installVagrantPlugin vagrant-phpstorm-tunnel 0.1.10
installVagrantPlugin vagrant-vbox-snapshot 0.0.4
installVagrantPlugin landrush 0.14.1
installVagrantPlugin vagrant-proxyconf 1.3.2
