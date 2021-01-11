COMMIT=$1
ENV_NAME=$2
PREVIOUS_COMMIT=$3
USER_NAME=$4
COMMENT=$5
ROLLBACK=$6

if [ ! -d "node_modules" ]; then
  npm install
fi

npm run prod

if [ ! -d "vendor" ]; then
  composer install --no-dev --classmap-authoritative --prefer-dist --no-scripts

  # Exit if composer failed
  if [ $? -ne 0 ]; then
    echo "Composer failed.";
    exit 1;
  fi
fi

composer dump-autoload --no-dev --classmap-authoritative --no-scripts

rm -rf node_modules/ tests/ resources/assets/ .git .deployment-state

mkdir compress

shopt -s extglob dotglob
mv !(compress) compress
shopt -u dotglob

cd compress

if [ -f ".env" ]; then
  mv .env ..
fi

echo "compressing compress dir"
# compress release
GZIP=-9 tar -czvf ../release.tar.gz .

cd ..

echo "compressed compress"

# delete files
rm -vrf !(".env"|"release.tar.gz")

echo "deleted compress dir"