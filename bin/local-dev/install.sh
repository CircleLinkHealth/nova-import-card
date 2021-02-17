#!/usr/bin/env bash

set -x

git config --local include.path $PWD/.gitconfig
bash "$PWD/bin/local-dev/setup-symlinks.sh"
bash "$PWD/bin/local-dev/setup-valet.sh"
bash "$PWD/bin/local-dev/composer.sh" install
bash "$PWD/bin/local-dev/npm.sh"

echo "CLH Monorepo installation finished! Build something awesome!"