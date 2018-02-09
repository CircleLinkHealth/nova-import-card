#!/bin/bash

BASEDIR=$(pwd)

cat "$BASEDIR"/bin/wkhtmtopdf/wkhtmltopdf > "$BASEDIR"/vendor/h4cc/wkhtmltopdf-amd64/bin/wkhtmltopdf
cat "$BASEDIR"/bin/wkhtmtopdf/wkhtmltopdf-amd64 > "$BASEDIR"/vendor/h4cc/wkhtmltopdf-amd64/bin/wkhtmltopdf-amd64

chmod 755 "$BASEDIR"/vendor/h4cc/wkhtmltopdf-amd64/bin/wkhtmltopdf
chmod 755 "$BASEDIR"/vendor/h4cc/wkhtmltopdf-amd64/bin/wkhtmltopdf-amd64

echo 'Done!'