#!/bin/bash

set -e

SHARED=$1
RELEASE=$2

# Create a shared vendor directory and symlink it to the project root
mkdir -p $SHARED/vendor
ln -s $SHARED/vendor $RELEASE/vendor


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

# Run migrations
php artisan migrate --force

# Exit if the migrations fail.
if [ $? -ne 0 ]; then
  echo "Migrations failed.";
  exit 1;
fi

# Enable lada-cache after migrations
# php artisan lada-cache:enable

php artisan view:clear
php artisan route:cache
php artisan config:cache

php artisan opcache:clear
php artisan opcache:optimize

# Restart Queue Workers
php artisan queue:restart
