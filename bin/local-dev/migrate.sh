#!/usr/bin/env bash

set -e
set -x

for SUBDOMAIN in $(ls "$PWD/apps/")
do
     APP_PATH="$PWD/apps/$SUBDOMAIN"
     if [[ "$APP_PATH" = *provider-app ]];then
          echo "$APP_PATH: Running migrations and generating view tables!"
          (cd $APP_PATH && php artisan migrate && php artisan migrate:views)
     fi
done