FROM laravelphp/vapor:php74

RUN apk add --update nodejs
RUN echo "NODE Version:" && node --version

COPY ./php/conf.d/php_staging.ini /usr/local/etc/php/conf.d/php_staging.ini

COPY . /var/task