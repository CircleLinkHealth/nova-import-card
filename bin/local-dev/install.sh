#!/usr/bin/env bash

set -x

bash "$PWD/bin/local-dev/setup-symlinks.sh"
bash "$PWD/bin/local-dev/setup-valet.sh"
bash "$PWD/bin/local-dev/composer.sh" clh
