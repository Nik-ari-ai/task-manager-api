FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    git \
    unzip \
    icu-dev \
    postgresql-dev \
    bash

RUN docker-php-ext-configure intl \
    && docker-php-ext-install \
        intl \
        pdo \
        pdo_pgsql \
        opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/app

COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]

CMD ["php-fpm"]
