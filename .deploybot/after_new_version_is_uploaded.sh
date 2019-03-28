#!/bin/bash

set -e

SHARED=$1
RELEASE=$2
PREVIOUS_REVISION=$3
REVISION=$4

if [ ! -d "$RELEASE/.git" ]; then
    git init && git remote add origin git@github.com:CircleLinkHealth/app-cpm-web.git
fi

git fetch

changed_files="$(git diff-tree -r --name-only --no-commit-id $PREVIOUS_REVISION $REVISION)"

echo "Files Changed"
echo $changed_files

if_file_changed() {
	echo "$changed_files" | grep --quiet "$1" && eval "$2"

	# fail deployment if there's an error
    if [ $? -ne 0 ]; then
      echo "`$2` failed.";
      exit 1;
    fi
}

if [ ! -d "$SHARED/node_modules" ]; then
  mkdir -p $SHARED/node_modules
fi

ln -s $SHARED/node_modules $RELEASE/node_modules

# install npm dependencies
if_file_changed package.json "npm install"

# compile assets
npm run prod

# fail deployment if there's an error
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