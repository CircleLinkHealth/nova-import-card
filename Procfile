release: php artisan migrate --force && php artisan migrate:views && php artisan deploy:post
web: vendor/bin/heroku-php-nginx -C heroku/nginx.conf public/
worker: php artisan horizon
scheduler: php artisan schedule:cron --queue
