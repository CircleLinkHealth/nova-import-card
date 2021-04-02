#!/usr/bin/env bash

set -e
set -x

(cd "$PWD/apps/provider-app" && php artisan key:generate && php artisan migrate:fresh --force && php artisan migrate:views)