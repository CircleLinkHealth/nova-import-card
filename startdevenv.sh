#!/bin/bash

set -e

docker-compose up --build --remove-orphans -d

docker-compose exec app npm run build-dev

echo 'Done! http://localhost:8085/'