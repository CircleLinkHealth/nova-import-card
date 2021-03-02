FROM laravelphp/vapor:php74

COPY ./php/conf.d/php_staging.ini /usr/local/etc/php/conf.d/php_staging.ini

COPY . /var/task