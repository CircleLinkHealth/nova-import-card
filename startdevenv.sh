#!/bin/bash

set -e

# Reset BASH time counter
SECONDS=0

docker-compose --env-file=.env up --build --remove-orphans -d

docker-compose exec app php artisan mysql:createdb cpm_tests

docker-compose exec app npm run build-dev

docker-compose exec app php artisan db:seed --class=TestSuiteSeeder

ELAPSED="Elapsed: $((($SECONDS / 60) % 60))min $(($SECONDS % 60))sec"

echo "Time elapsed: $ELAPSED seconds."

echo "Done! You may head to: http://localhost:8085"
