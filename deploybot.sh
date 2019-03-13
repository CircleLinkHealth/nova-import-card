#!/bin/bash

# Exit immediately if a command exits with a non-zero status.
set -e

SHARED=$1
RELEASE=$2

# Add github to known hosts
GH_HOST='github.com'
ssh-keygen -F $GH_HOST 2>/dev/null 1>/dev/null
if [ $? -eq 0 ]; then
    echo “$GH_HOST is already known”
else
    ssh-keyscan -t rsa -T 10 $GH_HOST >> ~/.ssh/known_hosts
fi


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
composer install --no-dev --classmap-authoritative --prefer-dist

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