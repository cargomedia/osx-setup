#!/bin/bash
set -e
cd $(dirname $0)/../../..

bash dev/tools/osx/_setup.sh

php dev/deploy/dev.php dns Sys_Install
