#!/usr/bin/env bash

set -e
set -x

APP=$1
COMMAND=$2

(cd "$PWD/apps/$APP-app" && $COMMAND)
