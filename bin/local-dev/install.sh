#!/usr/bin/env bash

set -x

git config --local include.path $PWD/.gitconfig
bash "$PWD/bin/local-dev/setup-symlinks.sh"
bash "$PWD/bin/local-dev/setup-valet.sh"
bash "$PWD/bin/local-dev/composer.sh"