release: php artisan migrate --force && php artisan migrate:views
web: vendor/bin/heroku-php-nginx -C heroku/nginx.conf public/
scheduler: php -d memory_limit=512M artisan schedule:cron
