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

(cd $APP_PATH && vapor env $REVIEW_APP_NAME --docker)


DOCKER_FILE=$REVIEW_APP_NAME".Dockerfile"
PROD_DOCKER_FILE='production.Dockerfile'

if [ -f "$APP_PATH/$DOCKER_FILE" ];
then
    echo "$APP_PATH: DOCKER_FILE found!"
else
    (cd $APP_PATH && touch DOCKER_FILE)
fi

(cd $APP_PATH && cp PROD_DOCKER_FILE DOCKER_FILE)

#TODO:
#edit vapor.yml review-app vars.
