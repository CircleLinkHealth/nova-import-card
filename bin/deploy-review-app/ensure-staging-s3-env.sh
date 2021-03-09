#!/usr/bin/env bash

set -e
set -x

APPS=$@

if [[ -z $APPS ]]
then
    echo "Please provide specific Apps names"
    exit 0
fi


appsArray=( $APPS )

for appName in "${appsArray[@]}";

do echo "$appName";
    APP_PATH="$PWD/apps/$appName-app"

    ENV_FILE="staging-deploy-s3.env"

    if [ -f "$APP_PATH/$ENV_FILE" ];
    then
        echo "$APP_PATH: ENV_FILE found!"
    else
      #make sure it exists - create from sample - to implement
    fi
done