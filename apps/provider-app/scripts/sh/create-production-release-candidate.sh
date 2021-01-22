#!/bin/bash

set -e

SHIELD_SPACE_NAME=sps-circlelink

#Buildpack URLs
BUILDPACK_PHP=https://github.com/heroku/heroku-buildpack-php
BUILDPACK_NODE=https://github.com/heroku/heroku-buildpack-nodejs
BUILDPACK_WKHTML_TO_PDF=https://github.com/chap/wkhtmltopdf-heroku-18-buildpack

CPM_WEB_PROD_APP=cpm-prod-web
NEW_PROD_APP=${1:-rc-$(date +%s)}
NEW_URL=rc
PROD_ENV_FILE=.env.prod
GIT_REMOTE="https://github.com/CircleLinkHealth/app-cpm-web"

# create a php app in heroku private space
heroku create $NEW_PROD_APP --remote production --space $SHIELD_SPACE_NAME --buildpack $BUILDPACK_PHP

# add additional buildpacks
heroku buildpacks:add $BUILDPACK_NODE --app $NEW_PROD_APP --index 2
heroku buildpacks:add $BUILDPACK_WKHTML_TO_PDF --app $NEW_PROD_APP --index 3

# attach redis from current prod
heroku addons:attach $CPM_WEB_PROD_APP::REDIS --app $NEW_PROD_APP

# pull config from current prod and add it to new app
heroku config:pull --app $CPM_WEB_PROD_APP --file $PROD_ENV_FILE
heroku config:push --app $NEW_PROD_APP --file $PROD_ENV_FILE

# enable automatic certificate management
heroku certs:auto:enable --app $NEW_PROD_APP

# add new domain
heroku domains:add ${NEW_URL}.careplanmanager.com --app $NEW_PROD_APP

# add new app to pipeline as a production app
heroku pipelines:add cpm-web --app $NEW_PROD_APP --stage production --remote $GIT_REMOTE
