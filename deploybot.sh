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
composer install --no-dev --classmap-authoritative

# Disable lada-cache before migrations
php artisan lada-cache:disable

# Run migrations
php artisan migrate --force

# Exit if the migrations fail.
if [ $? -ne 0 ]; then
  echo "Migrations failed.";
  exit 1;
fi

# Enable lada-cache after migrations
# php artisan lada-cache:enable

# Add new line at the end of .env file
echo "" >> .env

# Append version to .env
php artisan version:show --format=compact_no_build --suppress-app-name | cat <(echo -n "BUGSNAG_APP_VERSION=") - >> .env

# Perform post depoyment tasks
php artisan deploy:post

# Notify Bugsnag of release
php artisan bugsnag:deploy