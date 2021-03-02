#!/usr/bin/env bash

set -x

bash "$PWD/bin/deploy-review-app/create-env.sh"
bash "$PWD/bin/deploy-review-app/ensure-stagging-s3-env.sh"
bash "$PWD/bin/deploy-review-app/update-vars.sh"