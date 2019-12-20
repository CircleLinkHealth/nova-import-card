release: php artisan migrate --force && php artisan migrate:views
web: vendor/bin/heroku-php-nginx -C heroku/nginx.conf public/
worker: php artisan horizon
scheduler: php artisan schedule:cron --queue
