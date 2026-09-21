#!/bin/sh
set -eu

cd /app

if [ ! -x node_modules/.bin/vite ]; then
	echo 'Installing frontend dependencies...'
	npm ci --no-audit --no-fund
fi

echo 'Frontend ready.'
exec "$@"
