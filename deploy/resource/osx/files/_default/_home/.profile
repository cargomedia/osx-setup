source /usr/local/etc/bash_completion.d/git-prompt.sh
PS1='\u@\h:\w\[\e[0;34m\]$(__git_ps1 " (%s)")\[\e[m\]$ '

alias ll='ls -lsah'
alias ql='qlmanage -p 2>/dev/null'

if [ -f $(brew --prefix)/etc/bash_completion ]; then
    . $(brew --prefix)/etc/bash_completion
fi

# ruby executables
export PATH=$(brew --prefix ruby)/bin:$PATH

# rbenv
export RBENV_ROOT=/usr/local/var/rbenv
if which rbenv > /dev/null; then eval "$(rbenv init -)"; fi

# homebrew-cask
export HOMEBREW_CASK_OPTS="--appdir=/Applications"

# Set a valid locale for Python (see http://stackoverflow.com/questions/1830394/python-locale-strange-error-whats-going-on-here-exactly)
export LC_CTYPE="en_US.UTF-8"
