#!/bin/bash

set -e

DB_NAME=$1
# Reset BASH time counter
SECONDS=0

docker-compose --env-file=.env up --build --remove-orphans -d

if [[ ! -z "$DB_NAME" ]]; then
    echo "CREATING DB $DB_NAME"
    docker-compose exec app php artisan mysql:createdb $DB_NAME
fi

docker-compose exec app npm run build-dev

if [[ ! -z "$DB_NAME" ]]; then
    docker-compose exec app php artisan db:seed --class=TestSuiteSeeder
fi

ELAPSED="Elapsed: $((($SECONDS / 60) % 60))min $(($SECONDS % 60))sec"

echo "Time elapsed: $ELAPSED seconds."

echo "Done! You may head to: http://localhost:8080"
