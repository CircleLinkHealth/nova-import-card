#!/bin/bash

# this command is not complete

find ../../Modules/ -maxdepth 1 -type d \( ! -name . \) -exec bash -c "cd '{}' && git checkout master && git pull && git checkout -b test-lint" \;