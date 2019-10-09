#!/bin/bash

SHARED=$1
RELEASE=$2
COMMIT=$3
ENV_NAME=$4
PREVIOUS_COMMIT=$5
USER_NAME=$6
ROLLBACK=$7

set -e

# Fetch sensitive keys from secure S3
php $RELEASE/.deploybot/FetchKeysFromS3.php

if [ ! -d "node_modules" ]; then
  npm install
fi

if [ -d "node_modules" ]; then
    #install bower dependencies
    ./node_modules/bower/bin/bower -V install --allow-root
fi

npm run prod

if [ ! -d "vendor" ]; then
  composer install --no-dev --classmap-authoritative --prefer-dist --no-scripts

  # Exit if composer failed
  if [ $? -ne 0 ]; then
    echo "Composer failed.";
    exit 1;
  fi
fi

php artisan tickets:store $COMMIT $ENV_NAME $ROLLBACK $USER_NAME $PREVIOUS_COMMIT

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