FROM laravelphp/vapor:php74

RUN apk add stunnel

COPY ./php/conf.d/* /usr/local/etc/php/conf.d/
COPY ./redis/stunnel-redis-cli.conf /etc/stunnel/redis-cli.conf

RUN stunnel /etc/stunnel/redis-cli.conf

COPY . /var/task