release: php artisan reviewapp:create-db && php artisan migrate --force && php artisan migrate:views && php artisan reviewapp:seed-db && php artisan deploy:post
web: vendor/bin/heroku-php-nginx -C heroku/nginx.conf public/
worker: php artisan horizon
awvlistener: php artisan redis:subscribe
awvEnrollmentSurveyCompleted: php artisan redis:enrollmentCompleted
scheduler: php artisan schedule:cron --queue