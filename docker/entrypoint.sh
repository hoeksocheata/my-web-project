#!/bin/sh
set -e
cd /app
[ -f .env ] || cp .env.example .env
if [ -z "$APP_KEY" ] && ! grep -q '^APP_KEY=base64' .env; then php artisan key:generate --force; fi
if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then mkdir -p database && touch database/database.sqlite; fi
php artisan migrate --force
[ "${RUN_SEED:-false}" = "true" ] && php artisan db:seed --force || true
php artisan storage:link 2>/dev/null || true
exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"
