VAGRANT='vagrant'

function installVagrantPlugin {
  if (which $VAGRANT >/dev/null); then
    if ! ($VAGRANT plugin list | grep -q $1); then
      $VAGRANT plugin install $1
    fi
  fi
}

installVagrantPlugin vagrant-librarian-puppet
installVagrantPlugin vagrant-phpstorm-tunnel
installVagrantPlugin vagrant-vbox-snapshot
installVagrantPlugin vagrant-dns
installVagrantPlugin vagrant-proxyconf
