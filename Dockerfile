ARG PHP_VERSION=5.6
ARG COMPOSER_VERSION=2.2

FROM composer:${COMPOSER_VERSION} AS composer


FROM php:${PHP_VERSION}-fpm-alpine

ENV SYMFONY_ENV=prod

RUN set -eux \
    && apk update --no-cache \
    && apk upgrade --no-cache \
    && apk add --no-cache \
        acl \
        autoconf \
        g++ \
        make \
        postgresql-dev \
        su-exec \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
    && runDeps="$( \
        scanelf --needed --nobanner --format '%n#p' --recursive /usr/local/lib/php/extensions \
            | tr ',' '\n' \
            | sort -u \
            | awk 'system("[ -e /usr/local/lib/" $1 " ]") == 0 { next } { print "so:" $1 }' \
    )" \
    && apk add --no-cache --virtual .phpexts-rundeps $runDeps;


COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY docker/php/php.ini $PHP_INI_DIR/php.ini
COPY docker/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

WORKDIR /var/www/todo

COPY composer.json composer.json
COPY composer.lock composer.lock

RUN composer install --no-dev --no-autoloader --no-interaction --no-scripts

COPY . /var/www/todo

RUN set -eux \
    && mkdir -p app/cache \
    && composer dump-autoload --classmap-authoritative --no-dev --no-scripts --optimize \
    && php ./vendor/sensio/distribution-bundle/Resources/bin/build_bootstrap.php \
    && php bin/console cache:clear --no-warmup -vvv \
    && php bin/console cache:warmup \
    && php bin/console assets:install

ENTRYPOINT ["docker-entrypoint"]
CMD ["php-fpm"]