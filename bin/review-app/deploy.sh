#!/usr/bin/env bash

#TODO: UNFINISHED, PLEASE REFRAIN FROM USING
#set -e
#set -x
#
#REVIEW_APP_NAME=$1
#if [[ -z $REVIEW_APP_NAME ]]
#then
#    echo "Please provide a name for the review app."
#    exit 0
#fi
#
#shift
#
#APP_NAMES=$@
#
#if [[ -z $APP_NAMES ]]
#then
#    echo "Please provide specific App names. (As found in the monorepo apps directory, without the -app suffix)"
#    exit 0
#fi
#
#
#for APP_NAME in $APP_NAMES;
#do
#    bash "$PWD/bin/review-app/create-env.sh" $REVIEW_APP_NAME $APP_NAME
#    bash "$PWD/bin/review-app/ensure-staging-s3-env.sh" $APP_NAME
#    bash "$PWD/bin/review-app/update-vars.sh" $REVIEW_APP_NAME $APP_NAME
#done
#
#monovapor deploy:cpm $REVIEW_APP_NAME staging $APP_NAMES
#
#
#
