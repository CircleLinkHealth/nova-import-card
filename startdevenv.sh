#!/bin/bash

set -e

# Reset BASH time counter
SECONDS=0

# Fire up the container
docker-compose --env-file=.env up --build --remove-orphans -d

docker-compose exec cpm composer install

docker-compose exec cpm php artisan mysql:createdb

docker-compose exec cpm php artisan migrate

docker-compose exec cpm php artisan migrate:views

# Seed the database if a DB name was passed to the script
if [[ ! -z "$NEW_DB_NAME" ]]; then
    docker-compose exec cpm php artisan db:seed --class=TestSuiteSeeder
fi

docker-compose exec cpm npm install

docker-compose exec cpm npm run bower_install

ELAPSED="Elapsed: $((($SECONDS / 60) % 60))min $(($SECONDS % 60))sec"

echo "Time elapsed: $ELAPSED seconds."

echo "Done! You may head to: http://localhost:8080"
