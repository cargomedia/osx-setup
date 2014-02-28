#!/bin/bash
set -e

bash deploy/_setup.sh

php deploy/dev.php default Sys_Install
