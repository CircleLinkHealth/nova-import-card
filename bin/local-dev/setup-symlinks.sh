#!/usr/bin/env bash

set -e
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
            if [ -e "$APP_PATH/CircleLinkHealth/$DIR" ]; then
              rm -rf "$APP_PATH/CircleLinkHealth/$DIR"
            fi
            ln -s "$PWD/modules/$DIR" "$APP_PATH/CircleLinkHealth/$DIR"
        done
    else
        echo "$APP_PATH: monorepo-modules.txt not found!"
    fi

done
