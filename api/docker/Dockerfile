# syntax=docker/dockerfile:1.7

FROM dunglas/frankenphp:1-php8.3-bookworm AS base

WORKDIR /app

RUN install-php-extensions \
        apcu \
        intl \
        opcache \
        pdo_pgsql \
        zip

ENV PHP_INI_SCAN_DIR=":$PHP_INI_DIR/app.conf.d"

COPY docker/php/app.ini $PHP_INI_DIR/app.conf.d/10-app.ini
COPY --chmod=755 docker/docker-entrypoint.sh /usr/local/bin/docker-entrypoint

ENTRYPOINT ["docker-entrypoint"]
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]

FROM base AS composer

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip \
    && rm -rf /var/lib/apt/lists/* \
    && install-php-extensions @composer

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_HOME=/tmp/composer

FROM composer AS development

ENV APP_ENV=dev \
    APP_DEBUG=1

RUN cp "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

COPY docker/php/app.dev.ini $PHP_INI_DIR/app.conf.d/20-app.dev.ini
COPY docker/Caddyfile /etc/caddy/Caddyfile

FROM composer AS build

ENV APP_ENV=prod \
    APP_DEBUG=0

COPY composer.json composer.lock symfony.lock ./

RUN --mount=type=cache,target=/tmp/composer \
    composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-progress --no-interaction

COPY . .

# These placeholders only exist during the build; the container gets its real values at runtime.
RUN --mount=type=cache,target=/tmp/composer \
    export APP_SECRET=build-time-only \
        DATABASE_URL="postgresql://build:build@127.0.0.1:5432/build?serverVersion=16&charset=utf8" \
        DEFAULT_URI=http://localhost \
        MAILER_DSN=null://null \
        MAILER_FROM=noreply@shoppy.test \
        PAYMENT_PROVIDER=local \
        FRONTEND_BASE_URL=http://localhost \
        STRIPE_SECRET_KEY= \
        STRIPE_WEBHOOK_SECRET= \
    && printf "<?php\n\nreturn ['APP_ENV' => 'prod', 'APP_DEBUG' => '0', 'APP_SHARE_DIR' => 'var/share'];\n" > .env.local.php \
    && mkdir -p var/cache var/log var/share var/fixture-images public/uploads \
    && composer dump-autoload --classmap-authoritative --no-dev \
    && composer run-script --no-dev post-install-cmd \
    && php bin/console cache:warmup --env=prod --no-debug \
    && rm -rf docker .dockerignore .env .env.dev .env.test \
    && chmod -R u=rwX,go=rX . \
    && chmod 755 bin/console

FROM base AS production

ARG APP_UID=10001

ENV APP_ENV=prod \
    APP_DEBUG=0

# The API listens on 8080, so frankenphp does not need its bind capability (it breaks with cap_drop: ALL).
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && setcap -r /usr/local/bin/frankenphp \
    && useradd --uid "$APP_UID" --user-group --no-create-home --shell /usr/sbin/nologin app \
    && mkdir -p /data/caddy /config/caddy \
    && chown -R app:app /data /config

COPY docker/php/app.prod.ini $PHP_INI_DIR/app.conf.d/20-app.prod.ini
COPY docker/Caddyfile.prod /etc/caddy/Caddyfile
COPY --from=build /app /app

RUN chown -R app:app var public/uploads

USER app

EXPOSE 8080

HEALTHCHECK --interval=10s --timeout=5s --start-period=40s --retries=8 \
    CMD ["php", "-r", "exit(@file_get_contents('http://127.0.0.1:8080/products') === false ? 1 : 0);"]
