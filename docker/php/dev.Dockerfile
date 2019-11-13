FROM php:7.2-fpm

RUN apk add --no-cache $PHPIZE_DEPS && \
    pecl install xdebug && docker-php-ext-enable xdebug && \
    apt install zlib1g-dev libsqlite3-dev libpng-dev && \
    docker-php-ext-install zip pcntl exif bcmath gd pdo_mysql

VOLUME /app