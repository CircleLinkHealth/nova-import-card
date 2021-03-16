#!/usr/bin/env bash
#TODO: UNFINISHED, PLEASE REFRAIN FROM USING
#set -e
#set -x
#
#REVIEW_APP_NAME=$1
#
#if [[ -z $REVIEW_APP_NAME ]]
#then
#    echo "Please provide specific a Review APP name"
#    exit 0
#fi
#
#APP_NAME=$2
#
#if [[ -z $APP_NAME ]]
#then
#    echo "Please provide specific a APP name"
#    exit 0
#fi
#
#APP_PATH="$PWD/apps/$APP_NAME-app"
#
#(cd APP_PATH
#&&
#echo "
#APP_KEY=base64:tlVidPZw/dgUEpdYaOo9PahegypE3RQRRYrxg11uhZ0=
#APP_DEBUG=true
#CACHE_DRIVER=array
#MAIL_DRIVER=smtp
#MAIL_MAILER=postmark
#
#CPM_ADMIN_APP_URL=http://superadmin-$ENV_NAME.clh-staging.com
#CPM_PROVIDER_APP_URL=http://provider-$ENV_NAME.clh-staging.com
#
#SENTRY_LARAVEL_DSN=
#SENTRY_PROJECT=
#SCOUT_KEY=
#LOW_CPM_QUEUE_NAME=cpm-$APP_NAME-low-$REVIEW_APP_NAME
#HIGH_CPM_QUEUE_NAME=cpm-$APP_NAME-high-$REVIEW_APP_NAME
#REVISIONABLE_QUEUE=cpm-$APP_NAME-revisionable-staging
#APP_URL=https://$APP_NAME-$REVIEW_APP_NAME.clh-staging.com
#SESSION_DOMAIN=$APP_NAME-$REVIEW_APP_NAME.clh-staging.com
#SCOUT_MONITOR=false
#SCOUT_NAME=
#UNIQUE_ENV_NAME=$REVIEW_APP_NAME
#" > .env.$REVIEW_APP_NAME
#&&
#vapor env:push $REVIEW_APP_NAME )
#
#
#
#
