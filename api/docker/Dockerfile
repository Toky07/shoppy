# syntax=docker/dockerfile:1.7

FROM dunglas/frankenphp:1-php8.3-bookworm

WORKDIR /app

RUN apt-get update \
    && apt-get install -y --no-install-recommends git file \
    && install-php-extensions \
        @composer \
        apcu \
        intl \
        opcache \
        pdo_pgsql \
        pdo_sqlite \
        zip \
    && rm -rf /var/lib/apt/lists/*

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_HOME=/tmp/composer \
    PHP_INI_SCAN_DIR=":$PHP_INI_DIR/app.conf.d"

RUN cp "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

COPY docker/php/app.ini $PHP_INI_DIR/app.conf.d/10-app.ini
COPY docker/php/app.dev.ini $PHP_INI_DIR/app.conf.d/20-app.dev.ini
COPY docker/Caddyfile /etc/caddy/Caddyfile
COPY --chmod=755 docker/docker-entrypoint.sh /usr/local/bin/docker-entrypoint

ENTRYPOINT ["docker-entrypoint"]
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
