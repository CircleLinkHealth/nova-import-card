#!/usr/bin/env bash

set -e
set -x

ACTION=$1

if [[ -z $ACTION ]]
then
    ACTION=install
fi

for SUBDOMAIN in $(ls "$PWD/apps/")
do
    APP_PATH="$PWD/apps/$SUBDOMAIN"
    COMPOSER_FILE="composer.json"

    (cd $PWD && COMPOSER_MEMORY_LIMIT=-1 composer $ACTION)

    if [ -f "$APP_PATH/$COMPOSER_FILE" ]; then
        echo "$APP_PATH: COMPOSER_FILE found!"
        echo "$APP_PATH: running composer update"
        (cd $APP_PATH && COMPOSER_MEMORY_LIMIT=-1 composer $ACTION)
    fi

done
