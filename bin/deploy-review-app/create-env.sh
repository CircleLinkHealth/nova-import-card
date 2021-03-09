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

    (cd $APP_PATH && vapor env my-environment --docker)


    DOCKER_FILE="IMPLEMENT APP NAME"

    if [ -f "$APP_PATH/$DOCKER_FILE" ];
    then
        echo "$APP_PATH: DOCKER_FILE found!"
    else
      #make sure it exists - create from sample - to implement
    fi


    #check dockerfile was created
    #pass prod file data inside as contents using cat
done