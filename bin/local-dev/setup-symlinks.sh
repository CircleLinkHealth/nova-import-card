#!/usr/bin/env bash

set -e
set -x

for SUBDOMAIN in $(ls "$PWD/apps/")
do
    if [ ! -e "$PWD/apps/$SUBDOMAIN/CircleLinkHealth" ]; then
      ln -s "$PWD/modules/" "$PWD/apps/$SUBDOMAIN/CircleLinkHealth"
    fi
done
