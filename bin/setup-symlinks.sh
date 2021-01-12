#!/usr/bin/env bash

set -e
set -x

for SUBDOMAIN in $(ls "$PWD/apps/")
do
  ln -s "$PWD/modules/" "$PWD/apps/$SUBDOMAIN/CircleLinkHealth"
done
