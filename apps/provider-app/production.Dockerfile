FROM laravelphp/vapor:php74

RUN apk add --update nodejs
RUN echo "NODE Version:" && node --version

RUN pecl install scoutapm

COPY ./php/conf.d/php_production.ini /usr/local/etc/php/conf.d/php_production.ini

COPY . /var/task