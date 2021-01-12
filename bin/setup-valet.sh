#!/usr/bin/env bash

set -e
set -x

# example: cpm
DOMAIN_NAME=$1

for SUBDOMAIN in $(cat "$PWD/apps/")
do
  URL="$SUBDOMAIN.$DOMAIN"
  
  cd "$PWD/apps/$SUBDOMAIN"
  valet unlink $URL
  valet park $URL
done
