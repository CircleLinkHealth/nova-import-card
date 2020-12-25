FROM laravelphp/vapor:php74

RUN pecl install scoutapm

COPY ./php/conf.d/* /usr/local/etc/php/conf.d/

COPY . /var/task