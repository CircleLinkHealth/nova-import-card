#!/usr/bin/env bash

set -e
set -x

(cd "$PWD/apps/provider-app" && php artisan migrate && php artisan migrate:views)