#!/bin/bash

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

# Enable lada-cache after migrations
# php artisan lada-cache:enable

php artisan route:clear
php artisan view:clear
php artisan config:clear

php artisan route:cache
# php artisan config:cache

php artisan opcache:clear
php artisan opcache:optimize

# Restart Queue Workers
php artisan queue:restart
