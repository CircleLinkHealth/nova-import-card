#!/bin/bash

set -e

SHARED=$1
RELEASE=$2

# install npm dependencies
npm install

# fail depoyment if there's an error
if [ $? -ne 0 ]; then
  echo "`npm install` failed.";
  exit 1;
fi

# install bower dependencies
./node_modules/bower/bin/bower -V install

# fail depoyment if there's a bower error
if [ $? -ne 0 ]; then
  echo "`./node_modules/bower/bin/bower -V install` failed.";
  exit 1;
fi

# compile assets
npm run prod

# fail depoyment if there's an error
if [ $? -ne 0 ]; then
  echo "`npm run prod` failed.";
  exit 1;
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

# fail depoyment if there's an error
if [ $? -ne 0 ]; then
  echo "`composer install --no-dev --classmap-authoritative --prefer-dist` failed.";
  exit 1;
fi

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
php artisan lada-cache:enable

# Add new line at the end of .env file
echo "" >> .env

# Append version to .env
php artisan version:show --format=compact --suppress-app-name | cat <(echo -n "BUGSNAG_APP_VERSION=") - >> .env
php artisan version:show --format=compact --suppress-app-name | cat <(echo -n "APP_VERSION=") - >> .env

# Perform post depoyment tasks
php artisan deploy:post

# Notify Bugsnag of release
php artisan bugsnag:deploy