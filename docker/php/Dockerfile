FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
    bash git unzip icu-dev libzip-dev postgresql-dev \
    zlib-dev libmemcached-dev \
    linux-headers

RUN docker-php-ext-install pdo pdo_pgsql intl zip opcache sockets

# memcached extension
RUN apk add --no-cache $PHPIZE_DEPS \
    && pecl install memcached \
    && docker-php-ext-enable memcached \
    && apk del $PHPIZE_DEPS

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
