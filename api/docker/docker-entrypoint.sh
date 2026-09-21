#!/bin/sh
set -eu

cd /app

if [ "${1:-}" = 'frankenphp' ] || [ "${1:-}" = 'php' ] || [ "${1:-}" = 'bin/console' ]; then
	mkdir -p var/cache var/log var/fixture-images public/uploads

	if [ "${APP_ENV:-dev}" != 'prod' ]; then
		lock_hash="$(sha256sum composer.lock | awk '{print $1}')"
		installed_hash="$(cat vendor/.lock-hash 2>/dev/null || true)"

		if [ ! -d vendor/bin ] || [ "$lock_hash" != "$installed_hash" ]; then
			echo 'Installing PHP dependencies...'
			composer install --prefer-dist --no-progress --no-interaction --no-ansi
			echo "$lock_hash" > vendor/.lock-hash
		fi
	fi

	if [ -n "${DATABASE_URL:-}" ]; then
		echo 'Waiting for the database...'
		attempts=60
		until php bin/console dbal:run-sql -q 'SELECT 1' >/dev/null 2>&1; do
			attempts=$((attempts - 1))
			if [ "$attempts" -le 0 ]; then
				echo 'Database is not reachable.' >&2
				exit 1
			fi
			sleep 1
		done

		echo 'Running migrations...'
		php bin/console doctrine:migrations:migrate --no-interaction --all-or-nothing
	fi

	echo 'API ready.'
fi

exec "$@"
