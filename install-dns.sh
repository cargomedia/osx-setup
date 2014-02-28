#!/bin/bash
set -e

bash deploy/_setup.sh

php deploy/dev.php dns Sys_Install
