GEM='/usr/local/opt/ruby/bin/gem'

function gemInstall {
  if [ -n "${2}" ]; then
    if ! (${GEM} list --installed --version "${2}" "${1}"); then
      ${GEM} install ${1} --version ${2}
    fi
  else
    if ! (${GEM} list --installed "${1}"); then
      ${GEM} install ${1}
    else
      ${GEM} update ${1}
      ${GEM} cleanup ${1}
    fi
  fi
}

${GEM} update --system

gemInstall bundler
gemInstall fontcustom
gemInstall capistrano
gemInstall pulsar
gemInstall puppet
gemInstall librarian-puppet 2.2.1
gemInstall jekyll
gemInstall htty
gemInstall travis
