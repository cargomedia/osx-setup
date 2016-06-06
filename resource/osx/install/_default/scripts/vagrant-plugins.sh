VAGRANT='vagrant'

function installVagrantPlugin {
  if (which $VAGRANT >/dev/null); then
    if ! ($VAGRANT plugin list | grep -q "${1} (${2})"); then
      $VAGRANT plugin install "${1}" --plugin-version "${2}"
    fi
  fi
}

installVagrantPlugin vagrant-librarian-puppet 0.9.0
installVagrantPlugin vagrant-phpstorm-tunnel 0.1.10
installVagrantPlugin landrush 1.0.0
installVagrantPlugin vagrant-proxyconf 1.5.0
