#!/bin/sh
set -e
cd /app
[ -f .env ] || cp .env.example .env
if [ -z "$APP_KEY" ] && ! grep -q '^APP_KEY=base64' .env; then php artisan key:generate --force; fi
if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then mkdir -p database && touch database/database.sqlite; fi
php artisan migrate --force
[ "${RUN_SEED:-false}" = "true" ] && php artisan db:seed --force || true
php artisan storage:link 2>/dev/null || true
# ប្រើ PHP built-in server ផ្ទាល់ (artisan serve លុប Environment Variables របស់ Render ចោល)
cd /app/public
exec php -S 0.0.0.0:"${PORT:-10000}" -t /app/public /app/vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php