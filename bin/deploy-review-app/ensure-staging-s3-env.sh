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
    (cd APP_PATH &&
#don't know if I should add the actual values here so they can go to git. I'm guessing no right?
echo "
S3_SECRETS_SECRET=
S3_SECRETS_BUCKET=cpm-staging-keys
S3_SECRETS_KEY=
S3_SECRETS_REGION=us-east-1
ENV_TYPE=staging
APP_NAME=$APP_NAME" > ENV_FILE
fi
