#!/usr/bin/env bash

set -e
set -x

ACTION=$1

if [[ -z $ACTION ]]
then
    ACTION=install
fi

(cd "$PWD/modules/VaporCli" && COMPOSER_MEMORY_LIMIT=-1 composer $ACTION)
