release: php artisan migrate --force && php artisan deploy:post
web: vendor/bin/heroku-php-nginx -C heroku/nginx.conf public/
worker: php artisan horizon
scheduler: php -d memory_limit=512M artisan schedule:cron