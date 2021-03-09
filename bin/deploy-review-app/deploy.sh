#!/usr/bin/env bash

set -e
set -x

APPS=$@

if [[ -z $APPS ]]
then
    echo "Please provide specific App names"
    exit 0
fi

bash "$PWD/bin/deploy-review-app/create-env.sh" $APPS
bash "$PWD/bin/deploy-review-app/ensure-staging-s3-env.sh" $APPS
bash "$PWD/bin/deploy-review-app/update-vars.sh" $APPS