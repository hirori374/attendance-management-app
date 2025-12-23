FROM php:8.2-fpm

COPY php.ini /usr/local/etc/php/

RUN apt update \
  && apt install -y default-mysql-client zlib1g-dev libzip-dev unzip curl git \
  && docker-php-ext-install pdo_mysql zip

RUN set -eux; \
  curl -sS https://getcomposer.org/installer -o composer-setup.php; \
  php composer-setup.php --install-dir=/usr/local/bin --filename=composer; \
  rm composer-setup.php

WORKDIR /var/www