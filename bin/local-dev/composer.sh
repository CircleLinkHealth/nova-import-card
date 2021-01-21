#!/usr/bin/env bash

set -e
set -x

for SUBDOMAIN in $(ls "$PWD/apps/")
do
    APP_PATH="$PWD/apps/$SUBDOMAIN"
    COMPOSER_FILE="composer.json"

    if [ -f "$APP_PATH/$COMPOSER_FILE" ]; then
        echo "$APP_PATH: COMPOSER_FILE found!"

        (cd $APP_PATH && COMPOSER_MEMORY_LIMIT=-1 composer update)
    fi

done
