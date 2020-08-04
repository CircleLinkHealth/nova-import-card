release: php artisan reviewapp:create-db && php artisan migrate --force && php artisan migrate:views && php artisan reviewapp:seed-db && php artisan deploy:post
web: vendor/bin/heroku-php-nginx -C heroku/nginx.conf -F heroku/php_custom.conf public/
worker: php artisan horizon
awvlistener: php artisan redis:subscribe
scheduler: php artisan schedule:cron --queue