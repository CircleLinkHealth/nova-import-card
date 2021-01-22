#!/bin/bash

export MONOREPO_NAME=cpm-monorepo

cd ../

cat ./$MONOREPO_NAME/repos.txt | ./tomono.sh --continue

cp -f ./$MONOREPO_NAME/.git/config ./$MONOREPO_NAME/.gitconfig
