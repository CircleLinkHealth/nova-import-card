#!/usr/bin/env bash

set -x

cp -f "$PWD/bin/local-dev/git/config" "$PWD/.git/config"
bash "$PWD/bin/local-dev/setup-symlinks.sh"
bash "$PWD/bin/local-dev/setup-valet.sh"
bash "$PWD/bin/local-dev/composer.sh"