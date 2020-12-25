FROM laravelphp/vapor:php74

RUN pecl install scoutapm

COPY ./php/conf.d/php_staging.ini /usr/local/etc/php/conf.d/

COPY . /var/task