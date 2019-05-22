COMMIT=$1
ENV_NAME=$2
PREVIOUS_COMMIT=$3
USER_NAME=$4
COMMENT=$5
ROLLBACK=$6

if [ ! -d "node_modules" ]; then
  npm install
fi

#install bower dependencies
./node_modules/bower/bin/bower -V install --allow-root

npm run prod

if [ ! -d "vendor" ]; then
  composer install --no-dev --classmap-authoritative --prefer-dist
fi

php artisan tickets:store $COMMIT $ENV_NAME $ROLLBACK $USER_NAME $COMMENT $PREVIOUS_COMMIT

rm -rf node_modules/ scripts/ tests/ resources/assets/ .git .circleci .deployment-state app/Console/DevCommands/

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