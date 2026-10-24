
# syntax=docker/dockerfile:1.7

FROM composer:2 AS composer-bin

FROM php:8.3-fpm-alpine AS php-base

RUN apk add --no-cache \
    postgresql-dev \
    icu-dev \
    libzip-dev \
    oniguruma-dev \
    $PHPIZE_DEPS \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_pgsql \
        mbstring \
        bcmath \
        intl \
        opcache \
        zip \
    && apk del $PHPIZE_DEPS

WORKDIR /var/www/html

COPY docker/php/www.conf /usr/local/etc/php-fpm.d/zz-app.conf

FROM php-base AS dependencies

COPY --from=composer-bin /usr/bin/composer /usr/bin/composer
COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

FROM php-base AS php

COPY --from=composer-bin /usr/bin/composer /usr/bin/composer
COPY --from=dependencies /var/www/html/vendor ./vendor
COPY . .

RUN composer dump-autoload --optimize --no-dev \
    && mkdir -p storage/framework/cache \
       storage/framework/sessions \
       storage/framework/views \
       storage/logs \
       bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwX storage bootstrap/cache

USER www-data

EXPOSE 9000

CMD ["php-fpm", "-F"]

FROM nginx:1.28-alpine AS nginx

WORKDIR /var/www/html

COPY --from=php /var/www/html /var/www/html
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

EXPOSE 8080

CMD ["nginx", "-g", "daemon off;"]
