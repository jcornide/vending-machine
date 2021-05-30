FROM php:7.4-fpm-alpine

RUN apk update \
    && apk add  --no-cache \
    icu-dev \
    libxml2-dev \
    g++ \
    make \
    autoconf \
    && docker-php-source extract \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && rm -rf /tmp/*

WORKDIR /usr/src/app

COPY . /usr/src/app

RUN composer install --ignore-platform-reqs
