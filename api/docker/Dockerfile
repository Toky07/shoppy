# syntax=docker/dockerfile:1.7

FROM dunglas/frankenphp:1-php8.3-bookworm AS base

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

COPY docker/php/app.ini $PHP_INI_DIR/app.conf.d/10-app.ini
COPY --chmod=755 docker/docker-entrypoint.sh /usr/local/bin/docker-entrypoint

ENTRYPOINT ["docker-entrypoint"]
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]

FROM base AS development

ENV APP_ENV=dev \
    APP_DEBUG=1

RUN cp "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

COPY docker/php/app.dev.ini $PHP_INI_DIR/app.conf.d/20-app.dev.ini
COPY docker/Caddyfile /etc/caddy/Caddyfile

FROM base AS production

ENV APP_ENV=prod \
    APP_DEBUG=0 \
    APP_SECRET=build-time-only \
    DATABASE_URL="sqlite:////app/var/data.db" \
    PAYMENT_PROVIDER=local \
    STRIPE_SECRET_KEY="" \
    STRIPE_WEBHOOK_SECRET="" \
    DEFAULT_URI=http://localhost

RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY docker/php/app.prod.ini $PHP_INI_DIR/app.conf.d/20-app.prod.ini
COPY docker/Caddyfile.prod /etc/caddy/Caddyfile
COPY composer.json composer.lock ./

RUN --mount=type=cache,target=/tmp/composer \
    composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-progress --no-interaction

COPY . .

RUN --mount=type=cache,target=/tmp/composer \
    mkdir -p var/cache var/log public/uploads \
    && composer dump-autoload --classmap-authoritative --no-dev \
    && composer run-script --no-dev post-install-cmd \
    && php bin/console cache:warmup --env=prod --no-debug \
    && chmod +x bin/console
