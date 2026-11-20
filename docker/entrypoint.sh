#!/bin/sh
set -e
cd /var/www/html

if [ -f artisan ]; then
  if [ ! -d vendor ]; then
    echo "[entrypoint] vendor/ not found: running composer install (first start takes a minute or two)..."
    composer install --no-interaction --prefer-dist
  fi

  echo "[entrypoint] running database migrations..."
  php artisan migrate --force || echo "[entrypoint] migrate failed, read the error above"

  mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
  chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
  chmod -R ug+rwX storage bootstrap/cache 2>/dev/null || true
else
  echo "[entrypoint] No Laravel app found in src/ yet. See docs/LEADER_SETUP.md"
fi

exec "$@"
