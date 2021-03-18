#!/usr/bin/env bash

set -e
set -x

for SUBDOMAIN in $(ls "$PWD/apps/")
do
    APP_PATH="$PWD/apps/$SUBDOMAIN"
    BLUEPRINT_ENV_FILE=".env.example"
    ENV_FILE = ".env"

    if [ ! -f "$APP_PATH/$ENV_FILE" ]; then
        echo "$APP_PATH: .env not found!"
        echo "$APP_PATH: Creating .env from .env.example"
        (cd $APP_PATH && (cp $BLUEPRINT_ENV_FILE $ENV_FILE))
    fi
done