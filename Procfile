release: php artisan heroku:onrelease
web: vendor/bin/heroku-php-nginx -C heroku/nginx.conf public/
worker: php artisan horizon
scheduler: php artisan schedule:cron --queue