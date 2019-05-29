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
    cd $RELEASE/storage
    rm -rf app debugbar download exports framework logs patient pdfs
    cd $RELEASE

    mv $RELEASE/storage/* $SHARED/storage/
    echo "ran mv $RELEASE/storage/* $SHARED/storage/"

    chmod -R 775 $SHARED/storage
    chmod -R g+s $SHARED/storage

    rm -rf storage
fi

if [ ! -L "$RELEASE/storage" ]; then
    ln -s $SHARED/storage $RELEASE/storage
    echo "symlinked $RELEASE/storage to $SHARED/storage"
fi

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
php artisan responsecache:clear