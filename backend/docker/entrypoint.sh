#!/bin/sh
set -e

cd /var/www/html

if [ ! -f .env ]; then
  echo "No backend/.env found. Copy .env.example to .env and set APP_KEY / DB_* before starting."
  exit 1
fi

if [ ! -d vendor ] || [ ! -f vendor/autoload.php ]; then
  echo "Installing PHP dependencies..."
  composer install --no-interaction --prefer-dist
fi

if ! grep -qE '^APP_KEY=base64:' .env 2>/dev/null; then
  echo "Generating APP_KEY..."
  php artisan key:generate --force --no-interaction
fi

exec "$@"
