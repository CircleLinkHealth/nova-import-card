#!/bin/bash

set -e

SHARED=$1
RELEASE=$2


# Create a shared storage directory and symlink it to the project root
if [ ! -d "$SHARED/storage" ]; then
  mkdir -p $SHARED/storage
  mv storage/* $SHARED/storage/
  chmod -R 775 $SHARED/storage
  chmod -R g+s $SHARED/storage
fi

rm -rf storage
ln -s $SHARED/storage $RELEASE/storage

# Install application dependencies
composer install
composer dumpautoload -a

# Disable lada-cache before migrations
php artisan lada-cache:disable

# Enable lada-cache after migrations
# php artisan lada-cache:enable

php artisan deploy:post
