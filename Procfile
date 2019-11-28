release: php artisan migrate --force && php artisan migrate:views && php artisan deploy:post
web: vendor/bin/heroku-php-nginx public/
worker: php artisan horizon
scheduler: php -d memory_limit=512M artisan schedule:cron