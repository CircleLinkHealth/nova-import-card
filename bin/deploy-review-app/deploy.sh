#!/usr/bin/env bash

set -e
set -x

APPS=$@

if [[ -z $APPS ]]
then
    echo "Please provide specific Apps names"
    exit 0
fi

bash "$PWD/bin/deploy-review-app/create-env.sh" $@
bash "$PWD/bin/deploy-review-app/ensure-stagging-s3-env.sh"
bash "$PWD/bin/deploy-review-app/update-vars.sh"