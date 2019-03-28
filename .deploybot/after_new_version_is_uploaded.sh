#!/bin/bash

set -e

SHARED=$1
RELEASE=$2
PREVIOUS_REVISION=$3
REVISION=$4
BRANCH=$5
RELEASE_ID=$6

if [ -z "$PREVIOUS_REVISION" || -z "$REVISION" ]; then
    changed_files=""
else
    changed_files="$(git diff-tree -r --name-only --no-commit-id $PREVIOUS_REVISION $REVISION)"
fi

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

# install npm dependencies
if_file_changed package.lock "npm install"

# compile assets
if_file_changed resources/assets "npm run prod"

# Create a shared storage directory and symlink it to the project root
if [ ! -d "$SHARED/storage" ]; then
  mkdir -p $SHARED/storage
  mv storage/* $SHARED/storage/
  chmod -R 775 $SHARED/storage
  chmod -R g+s $SHARED/storage
fi

if [ ! -d "$RELEASE/storage" ]; then
    mkdir storage
fi

if [! -L "$RELEASE/storage"]; then
    ln -s $SHARED/storage $RELEASE/storage
    echo "$RELEASE/storage symlinked to $SHARED/storage"
fi


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