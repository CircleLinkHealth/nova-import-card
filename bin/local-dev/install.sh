#!/usr/bin/env bash

set -x

bash "$PWD/bin/local-dev/setup-gitconfig.sh"
bash "$PWD/bin/local-dev/setup-symlinks.sh"
bash "$PWD/bin/local-dev/setup-valet.sh"
bash "$PWD/bin/local-dev/composer.sh" install
bash "$PWD/bin/local-dev/npm.sh"
bash "$PWD/bin/local-dev/migrate.sh"

echo "CLH Monorepo installation finished! Build something awesome!"