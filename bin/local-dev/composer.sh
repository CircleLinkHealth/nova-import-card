#!/usr/bin/env bash

set -e
set -x

ACTION=$1

if [[ -z $ACTION ]]
then
    ACTION=install
fi

if [ $ACTION == 'hard-reset' ];then
    ACTION=install;
fi

SHOULD_RESET=false;
if [  $1 == 'hard-reset' || $2 == 'hard-reset' ]; then
    SHOULD_RESET=true;
fi


if [ $SHOULD_RESET ]; then
    for SUBDOMAIN in $(ls "$PWD/apps/")
    do
      APP_PATH="$PWD/apps/$SUBDOMAIN"
      COMPOSER_LOCK="composer.lock"

      if [ $SHOULD_RESET ]; then
          echo "$SUBDOMAIN: Deleting vendor folder."
          rm -rf "$APP_PATH/vendor"
          echo "$SUBDOMAIN: Deleted vendor folder."

      if [ -f "$APP_PATH/$COMPOSER_LOCK" ]; then
          echo "$APP_PATH: COMPOSER_LOCK found!"
          echo "$SUBDOMAIN: Deleting $COMPOSER_LOCK"
          rm "$APP_PATH/$COMPOSER_LOCK";
          echo "$SUBDOMAIN: Deleted $COMPOSER_LOCK"
      fi
    fi
    done
    (cd $PWD && COMPOSER_MEMORY_LIMIT=-1 composer clear-cache)
fi

for SUBDOMAIN in $(ls "$PWD/apps/")
do
    APP_PATH="$PWD/apps/$SUBDOMAIN"
    COMPOSER_JSON="composer.json"

    (cd $PWD && COMPOSER_MEMORY_LIMIT=-1 composer $ACTION)

    if [ -f "$APP_PATH/$COMPOSER_JSON" ]; then
        echo "$APP_PATH: COMPOSER_JSON found!"
        echo "$APP_PATH: running composer install"
        (cd $APP_PATH && COMPOSER_MEMORY_LIMIT=-1 composer $ACTION)
    fi
done
