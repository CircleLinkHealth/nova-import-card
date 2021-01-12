#!/usr/bin/env bash

set -x


for SUBDOMAIN in $(ls "$PWD/apps/")
do
    APP_PATH="$PWD/apps/$SUBDOMAIN"

    if [ -f "$APP_PATH/monorepo-modules.txt" ]; then
        echo "$APP_PATH: monorepo-modules.txt found!"
        for DIR in $(cat "$APP_PATH/monorepo-modules.txt")
        do
            if [ ! -d "$APP_PATH/CircleLinkHealth" ]; then
              mkdir "$APP_PATH/CircleLinkHealth"
            fi

            echo "Installing $DIR in $APP_PATH"

            if [ -e "$APP_PATH/CircleLinkHealth/$DIR" ]; then
                echo "Deleting existing $APP_PATH/CircleLinkHealth/$DIR"
                rm -rf "$APP_PATH/CircleLinkHealth/$DIR"
            fi

            echo "Copying modules"

            cp -rf "$PWD/modules/$DIR" "$APP_PATH/CircleLinkHealth/$DIR"
        done
    fi
done