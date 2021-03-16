#!/usr/bin/env bash
#TODO: UNFINISHED, PLEASE REFRAIN FROM USING
#set -e
#set -x
#
#APP_NAME=$1
#
#if [[ -z $APP_NAME ]]
#then
#    echo "Please provide specific a APP name"
#    exit 0
#fi
#
#APP_PATH="$PWD/apps/$APP_NAME-app"
#
#ENV_FILE="staging-deploy-s3.env"
#
#if [ -f "$APP_PATH/$ENV_FILE" ];
#then
#    echo "$APP_PATH: ENV_FILE found!"
#else
#    (cd APP_PATH &&
#echo "
#S3_SECRETS_SECRET=1QfZhQDi8Ihxh67VY4Pk69Sx1vsWefZfjLf9+K/v
#S3_SECRETS_BUCKET=cpm-staging-keys
#S3_SECRETS_KEY=AKIAZYB3F7ZGBKRUHG5Y
#S3_SECRETS_REGION=us-east-1
#ENV_TYPE=staging
#APP_NAME=$APP_NAME" > ENV_FILE
#fi
