#!/bin/bash

git checkout development && git pull && git checkout worker-master && git pull && git merge development && git push && git checkout master && git pull && git merge development && git push && git checkout development