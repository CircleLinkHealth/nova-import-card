#!/usr/bin/env bash

set -e

# Make sure the release tag is provided.
if (( "$#" != 1 ))
then
    echo "Tag has to be provided."

    exit 1
fi

RELEASE_BRANCH=$1
CURRENT_BRANCH=$(git rev-parse --abbrev-ref HEAD)
VERSION=$1

# Always prepend with "release-"
if [[ $RELEASE_BRANCH != release-*  ]]
then
    echo "Release branches must begin with `release-`."

    exit 1
fi

# Removes lines containing CircleLinkHealth from git .gitignore
# so we can copy modules and have them committed
sed -i '' '/CircleLinkHealth/d' $PWD/.gitignore

# Make sure current branch and release branch match.
if [[ "$RELEASE_BRANCH" != "$CURRENT_BRANCH" ]]
then
    echo "Release branch ($RELEASE_BRANCH) does not match the current active branch ($CURRENT_BRANCH)."

    exit 1
fi

# Make sure the working directory is clear.
if [[ ! -z "$(git status --porcelain)" ]]
then
    echo "Your working directory is dirty. Did you forget to commit your changes?"

    exit 1
fi

# Make sure latest changes are fetched first.
git fetch origin

# Make sure that release branch is in sync with origin.
if [[ $(git rev-parse HEAD) != $(git rev-parse origin/$RELEASE_BRANCH) ]]
then
    echo "Your branch is out of date with its upstream. Did you forget to pull or push any changes before releasing?"

    exit 1
fi

# Always prepend with "v"
if [[ $VERSION != v*  ]]
then
    VERSION="v$VERSION"
fi

# Tag Framework
git tag $VERSION
git push origin --tags

# Tag Components
for REMOTE in $(ls ../apps ../modules)
do
    echo ""
    echo ""
    echo "Releasing $REMOTE";

    TMP_DIR="/tmp/laravel-split"
    REMOTE_URL="git@github.com:illuminate/$REMOTE.git"

    rm -rf $TMP_DIR;
    mkdir $TMP_DIR;

    (
        cd $TMP_DIR;

        git clone $REMOTE_URL .
        git checkout "$RELEASE_BRANCH";

        git tag $VERSION
        git push origin --tags
    )
done