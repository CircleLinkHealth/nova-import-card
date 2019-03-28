#!/bin/bash

set -e

SHARED=$1
RELEASE=$2
PREVIOUS_REVISION=$3
REVISION=$4

git init && git remote add origin git@github.com:CircleLinkHealth/app-cpm-web.git && git fetch

changed_files="$(git diff-tree -r --name-only --no-commit-id $PREVIOUS_REVISION $REVISION)"

if_file_changed() {
	echo "$changed_files" | grep --quiet "$1" && eval "$2"
}

# install npm dependencies
if_file_changed package.json "npm install"

# fail depoyment if there's an error
if [ $? -ne 0 ]; then
  echo "`npm install` failed.";
  exit 1;
fi

if_file_changed bower.json "npm run bower_install"

# fail depoyment if there's an error
if [ $? -ne 0 ]; then
  echo "`npm install` failed.";
  exit 1;
fi

# compile assets
if_file_changed resources/assets "npm run prod"

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
if_file_changed composer.lock "composer install --no-dev --classmap-authoritative --prefer-dist"

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
php artisan version:show --format=compact --suppress-app-name | cat <(echo -n "APP_VERSION=") - >> .env

# Perform post depoyment tasks
php artisan deploy:post