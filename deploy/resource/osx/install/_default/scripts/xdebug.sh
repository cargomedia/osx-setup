if ! grep -Fxq "xdebug.remote_port = 9000" "/usr/local/etc/php/5.4/conf.d/ext-xdebug.ini"; then
  cat <<EOF >> /usr/local/etc/php/5.4/conf.d/ext-xdebug.ini
xdebug.profiler_enable_trigger = 1
xdebug.remote_enable = 1
xdebug.remote_host = localhost
xdebug.remote_port = 9000
EOF
fi
