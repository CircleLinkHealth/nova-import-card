#!/bin/bash

set -e

SHARED=$1
RELEASE=$2
REVISION=$3
BRANCH=$4
RELEASE_ID=$5
PREVIOUS_REVISION=$6

# Create a shared storage directory and symlink it to the project root
if [ ! -d "$SHARED/storage" ]; then
  mkdir -p $SHARED/storage
  echo "created $SHARED/storage"
fi

if [ -d "$RELEASE/storage" ]; then
    echo "running rsync -avu $RELEASE/storage/ $SHARED/storage"

    # sync release storage files to shared storage
    rsync -avu $RELEASE/storage/ $SHARED/storage

    echo "ran rsync -avu $RELEASE/storage/ $SHARED/storage"

    chmod -R 775 $SHARED/storage
    chmod -R g+s $SHARED/storage

    rm -rf storage
fi

if [ ! -L "$RELEASE/storage" ]; then
    ln -s $SHARED/storage $RELEASE/storage
    echo "symlinked $RELEASE/storage to $SHARED/storage"
fi

# laravel needs these to run, and git does not clone empty folders
mkdir -p $RELEASE/storage/framework/{framework,sessions,views,cache}

# Fetch sensitive keys from secure S3
php .deploybot/FetchKeysFromS3.php

composer dump-autoload --no-dev --classmap-authoritative --no-scripts

# Run migrations
php artisan migrate --force

# Exit if the migrations fail.
if [ $? -ne 0 ]; then
  echo "Migrations failed.";
  exit 1;
fi

# Add new line at the end of .env file
echo "" >> .env

# Append version to .env
php artisan version:show --format=compact --suppress-app-name | cat <(echo -n "APP_VERSION=") - >> .env

# Perform post depoyment tasks
php artisan deploy:post

# Clear response cache
# CAUTION: This command will log users out
# php artisan user-cache:clear