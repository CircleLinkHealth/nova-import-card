#!/usr/bin/env bash

set -e
set -x

APPS=$@

if [[ -z $APPS ]]
then
    echo "Please provide specific Apps names"
    exit 0
fi


appsArray=( $APPS )

for i in "${appsArray[@]}";

do echo "$i";

done

#take review-app-name
#for admin and provider apps
#check if env exists
#if not create
#create docker file.
#vapor env my-environment --docker
#or copy production docker file contents and put in new dockerfile using cat