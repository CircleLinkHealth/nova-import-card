#!/usr/bin/env bash

set -e
set -x

ENV_TYPE=$1

SOURCE_ENV_FILE=$PWD/$ENV_TYPE-deploy-s3.env

if [ ! -f $SOURCE_ENV_FILE ]; then
    echo "$SOURCE_ENV_FILE does not exist"
    exit 1
fi

for APP in $(ls "$PWD/apps/")
do
  APP_PATH="$PWD/apps/$APP"
  if [ -f "$APP_PATH/vapor.yml" ]; then
      DEST="$PWD/apps/$APP/$ENV_TYPE-deploy-s3.env"
      cp $SOURCE_ENV_FILE $DEST
      APP_NAME=$(echo $APP | sed -e "s/-app//g")
      sed -i '' "s/APP_NAME=/APP_NAME=$APP_NAME/g" $DEST
      echo "created[$DEST]"
  fi
done
