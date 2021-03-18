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
#if [[ $(monovapor env:list | grep -q $REVIEW_APP_NAME) -eq 0 ]];
#then
#    echo "$REVIEW_APP_NAME already exists"
#    exit;
#fi
#
#(cd $APP_PATH && monovapor review-app $REVIEW_APP_NAME --docker)
#
#DOCKER_FILE=$REVIEW_APP_NAME".Dockerfile"
#PROD_DOCKER_FILE='production.Dockerfile'
#
#if [ -f "$APP_PATH/$DOCKER_FILE" ];
#then
#    echo "$APP_PATH: DOCKER_FILE found!"
#else
#    (cd $APP_PATH && cp PROD_DOCKER_FILE DOCKER_FILE)
#fi
#
