#!/bin/bash

SHARED=$1
RELEASE=$2

declare -a HOSTS=('github.com')

# Add github to known hosts
for host in $HOSTS; do
  ssh-keygen -F $host 2>/dev/null 1>/dev/null
  if [ $? -eq 0 ]; then
    echo “$host is already known”
    continue
   fi
   ssh-keyscan -t rsa -T 10 $host >> ~/.ssh/known_hosts
done


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