#!/bin/bash

export MONOREPO_NAME=.

cat repos.txt | bin/tomono.sh --continue
