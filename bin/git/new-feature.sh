#!/usr/bin/env bash

set -e
set -x

FEATURE=$1

if [[ ! -z "$(git status --porcelain)" ]]
then
    echo "Your working directory is dirty. Did you forget to commit your changes?"

    exit 1
fi

git checkout master
git pull
git checkout -b feature_$FEATURE
