#!/bin/bash

BASEDIR=$(pwd)

echo 'Make files executable'
chmod 755 "$BASEDIR"/bin/wkhtmltopdf/wkhtmltopdf
chmod 755 "$BASEDIR"/bin/wkhtmltopdf/wkhtmltopdf-amd64

echo 'Done!'