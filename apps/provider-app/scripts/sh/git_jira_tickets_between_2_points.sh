#!/bin/bash

FROM=$1
TO=$2

# Show unique Jira Issues worked on since the provided tag.
# Jira Issues begin with CPM-
git log --pretty=oneline $FROM...$TO  | perl -ne '{ /(CPM)-(\d+)/ && print "$1-$2\n" }' | sort | uniq

# if perls is not available, use
# git log --pretty=oneline $FROM...$TO | grep "CPM-" | sort | uniq
