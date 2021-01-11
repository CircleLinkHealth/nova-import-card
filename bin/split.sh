#!/usr/bin/env bash

set -e
set -x

CURRENT_BRANCH="1.x"

function split()
{
    SHA1=`./bin/splitsh-lite --prefix=$1`
    git push $2 "$SHA1:refs/heads/$CURRENT_BRANCH" -f
}

function remote()
{
    git remote add $1 $2 || true
}

git pull origin $CURRENT_BRANCH

remote admin-app git@github.com:CircleLinkHealth/app-cpm-admin.git
remote awv-app git@github.com:CircleLinkHealth/app-awv.git
remote provider-app git@github.com:CircleLinkHealth/app-cpm-web.git
remote admin-module git@github.com:CircleLinkHealth/cpm-admin-module.git
remote eligibility-module git@github.com:CircleLinkHealth/eligibility-module.git
remote self-enrollment-module git@github.com:CircleLinkHealth/self-enrollment-module.git

split 'apps/admin' admin-app
split 'apps/awv' awv-app
split 'apps/provider' provider-app
split 'modules/admin' admin-module
split 'modules/admin' eligibility-module
split 'modules/admin' self-enrollment-module
