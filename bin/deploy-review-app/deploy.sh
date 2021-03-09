#!/usr/bin/env bash

set -e
set -x

REVIEW_APP_NAME=$1
if [[ -z $REVIEW_APP_NAME ]]
then
    echo "Please provide a name for the review app."
    exit 0
fi

shift

APP_NAMES=$@

if [[ -z $APP_NAMES ]]
then
    echo "Please provide specific App names. (As found in the monorepo apps directory, without the -app suffix)"
    exit 0
fi


for APP_NAME in $APP_NAMES;
do
    bash "$PWD/bin/deploy-review-app/create-env.sh" $REVIEW_APP_NAME $APP_NAME
    bash "$PWD/bin/deploy-review-app/ensure-staging-s3-env.sh" $REVIEW_APP_NAME $APP_NAME
    bash "$PWD/bin/deploy-review-app/update-vars.sh" $REVIEW_APP_NAME $APP_NAME
done

#deploy as well?
monovapor deploy:cpm $REVIEW_APP_NAME staging $APP_NAMES



