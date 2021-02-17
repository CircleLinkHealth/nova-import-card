#!/usr/bin/env bash

set -e
set -x

FEATURE=$1

git checkout -b feature_$FEATURE
