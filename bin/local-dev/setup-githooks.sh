#!/usr/bin/env bash

BASEDIR=$(dirname "$BASH_SOURCE")

function copyHooksTo() {
  SCRIPTS_DIR=$BASEDIR/../git-hooks/
  echo "Copying GitHooks to "$1
  # copy git hooks to ./.git/hooks/ folder
  for FILE in $SCRIPTS_DIR/*; do
    if [ -d "$FILE" ]; then
      if [ -d "$1" ]; then
        cp -R $FILE $1
      fi
    else
      if [ -d "$1" ]; then
        install -m 755 "$FILE" $1
      fi
    fi
  done
}
copyHooksTo $BASEDIR/../../.git/hooks/
