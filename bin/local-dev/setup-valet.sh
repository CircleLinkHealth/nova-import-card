#!/usr/bin/env bash

set -e
set -x

# example: clh
DOMAIN_NAME=$1

if [ -z "$DOMAIN_NAME" ]; then
    DOMAIN_NAME=clh
fi

for SUBDOMAIN in $(ls "$PWD/apps/")
do
  URL="$SUBDOMAIN.$DOMAIN_NAME"

  (cd "$PWD/apps/$SUBDOMAIN" && valet unlink $URL && valet link $URL)
done
