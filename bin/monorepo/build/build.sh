#!/bin/bash

export MONOREPO_NAME=cpm

cd ../

cat ./$MONOREPO_NAME/repos.txt | ./tomono.sh --continue
