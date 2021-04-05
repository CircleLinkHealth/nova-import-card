#!/usr/bin/env bash

set -e
set -x

(cd "$PWD/apps/provider-app" && php artisan --env=testing test:prepare-test_suite-db && php artisan --env=testing migrate:fresh && php artisan --env=testing migrate:views && php artisan --env=testing db:seed --class=CircleLinkHealth\\Customer\\Database\\Seeders\\TestSuiteSeeder)