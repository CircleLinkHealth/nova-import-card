#!/bin/bash

set -e

# If this is argument is passed, the script will create a new DB and seed it
# example:
# bash startdevenv.sh cpm_tests
NEW_DB_NAME=$1

# Reset BASH time counter
SECONDS=0

# Fire up the container
docker-compose --env-file=.env up --build --remove-orphans -d

docker-compose exec app composer install

# Create a database if a DB name was passed to the script
if [[ ! -z "$NEW_DB_NAME" ]]; then
    echo "CREATING DB $NEW_DB_NAME"
    docker-compose exec app php artisan mysql:createdb $NEW_DB_NAME
fi

docker-compose exec app php artisan migrate

docker-compose exec app php artisan migrate:views

# Seed the database if a DB name was passed to the script
if [[ ! -z "$NEW_DB_NAME" ]]; then
    docker-compose exec app php artisan db:seed --class=TestSuiteSeeder
fi

docker-compose exec app npm install

docker-compose exec app npm run bower_install

ELAPSED="Elapsed: $((($SECONDS / 60) % 60))min $(($SECONDS % 60))sec"

echo "Time elapsed: $ELAPSED seconds."

echo "Done! You may head to: http://localhost:8080"
