#!/bin/bash

TAG=$1

# Show unique Jira Issues worked on since the provided tag.
# Jira Issues begin with CPM-
git log --pretty=oneline HEAD...$TAG  | perl -ne '{ /(CPM)-(\d+)/ && print "$1-$2\n" }' | sort | uniq