#!/usr/bin/env bash

set -e
set -x

REVIEW_APP_NAME=$1

if [[ -z $REVIEW_APP_NAME ]]
then
    echo "Please provide specific a Review APP name"
    exit 0
fi

APP_NAME=$2

if [[ -z $APP_NAME ]]
then
    echo "Please provide specific a APP name"
    exit 0
fi



APP_PATH="$PWD/apps/$APP_NAME-app"

ENV_FILE="staging-deploy-s3.env"

if [ -f "$APP_PATH/$ENV_FILE" ];
then
    echo "$APP_PATH: ENV_FILE found!"
else
    #make sure it exists - create from sample - to implement
fi
