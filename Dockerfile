FROM php:8.3-fpm-alpine

ADD ./.docker/php/php.ini /usr/local/etc/php/conf.d/40-custom.ini
ADD ./.docker/php/xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
ADD ./.docker/php/error_reporting.ini /usr/local/etc/php/conf.d/error_reporting.ini
ADD ./.docker/redis/redis.conf /usr/local/etc/redis.conf

WORKDIR /var/www/html

COPY .env /var/www/html/.env

RUN apk add --no-cache linux-headers libzip-dev curl-dev curl libmcrypt libmcrypt-dev openssh-client icu-dev libxml2-dev freetype-dev libpng-dev libjpeg-turbo-dev g++ make autoconf openssl
RUN docker-php-source extract
RUN pecl install xdebug redis
RUN docker-php-ext-enable xdebug
RUN docker-php-ext-enable redis
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install curl pdo pdo_mysql zip gd pcntl
RUN apk add --no-cache libintl icu icu-dev
RUN docker-php-ext-install intl
RUN apk del --no-cache gcc g++
RUN rm -rf /var/cache/apk/*

RUN apk add --no-cache nodejs npm
RUN apk add --no-cache bash

RUN docker-php-ext-install exif

# Install supervisor and cronie
RUN apk add --no-cache supervisor dcron

COPY ./.docker/supervisor/supervisord.conf /etc/supervisord.conf
COPY ./.docker/cron/laravel /etc/crontabs/root

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN sed -i s/www-data:x:82/www-data:x:1000/ /etc/passwd \
    && chown -R www-data:www-data /var/www/html

# Run supervisord and cron
CMD /usr/bin/supervisord -c /etc/supervisord.conf
