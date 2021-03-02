#!/usr/bin/env bash

set -e
set -x

for SUBDOMAIN in $(ls "$PWD/apps/")
do
    APP_PATH="$PWD/apps/$SUBDOMAIN"
    NPM_FILE="package.json"

    if [ -f "$APP_PATH/$NPM_FILE" ]; then
        echo "$APP_PATH: NPM_FILE found!"
        echo "$APP_PATH: running npm install"
        (cd $APP_PATH && npm install)
    fi

done
