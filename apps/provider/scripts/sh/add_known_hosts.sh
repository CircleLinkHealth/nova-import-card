#!/bin/bash

declare -a HOSTS=('github.com')

# Add known hosts
for host in $HOSTS; do
  ssh-keygen -F $host 2>/dev/null 1>/dev/null
  if [ $? -eq 0 ]; then
    echo “$host is already known”
    continue
   fi
   ssh-keyscan -t rsa -T 10 $host >> ~/.ssh/known_hosts
done