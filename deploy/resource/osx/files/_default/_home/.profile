if [ -d ~/.profile.d ]; then
    for P in ~/.profile.d/*; do . "${P}"; done
fi
