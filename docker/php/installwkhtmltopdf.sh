#!/usr/bin/env bash
# bin/compile <build-dir> <cache-dir>

set -e

BUILD_DIR=$1
CACHE_DIR=$2
ENV_DIR=$3

BIN_PATH="$BUILD_DIR/bin"
LIB_PATH="$BUILD_DIR/vendor/wkhtmltox/lib"
TMP_PATH="$BUILD_DIR/tmp"
mkdir -p $CACHE_DIR $BIN_PATH $LIB_PATH $TMP_PATH

if [[ -f "$ENV_DIR/WKHTMLTOPDF_DOWNLOAD_URL" ]]; then
  WKHTMLTOPDF_DOWNLOAD_URL=$(cat "$ENV_DIR/WKHTMLTOPDF_DOWNLOAD_URL")
else
  WKHTMLTOPDF_DOWNLOAD_URL="https://github.com/wkhtmltopdf/wkhtmltopdf/releases/download/0.12.5/wkhtmltox_0.12.5-1.bionic_amd64.deb"
fi

WKHTMLTOPDF_DEB="$CACHE_DIR/wkhtmltox.deb"
WKHTMLTOPDF_PATH="$TMP_PATH/usr/local"
WKHTMLTOPDF_BINARIES="$WKHTMLTOPDF_PATH/bin"
WKHTMLTOPDF_LIBS="$WKHTMLTOPDF_PATH/lib"

BIN_DIR=$(cd $(dirname $0); pwd)

if [ -f $WKHTMLTOPDF_DEB ]; then
  echo "-----> Using wkhtmltopdf from cache"
else
  echo "-----> Downloading wkhtmltopdf deb"
  curl -L $WKHTMLTOPDF_DOWNLOAD_URL -o $WKHTMLTOPDF_DEB
fi

echo "-----> Unpacking deb"
dpkg -x $WKHTMLTOPDF_DEB $TMP_PATH

echo "-----> Setting permissions"
chmod +x $WKHTMLTOPDF_BINARIES/*

echo "-----> Moving binaries to the right place"
mv $WKHTMLTOPDF_BINARIES/* $BIN_PATH/

echo "-----> Moving libs to the right place"
mv $WKHTMLTOPDF_LIBS/* $LIB_PATH/

echo "-----> Cleaning up"
rm -rf $WKHTMLTOPDF_PATH