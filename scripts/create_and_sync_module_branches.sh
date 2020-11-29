#!/bin/bash

# this command is not complete

BRANCH_NAME=$1

find ./CircleLinkHealth/ -maxdepth 1 -type d \( ! -name . \) -exec bash -c "cd '{}' && git checkout master && git pull && git checkout $BRANCH_NAME && git pull && git merge master && git push" \;