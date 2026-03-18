#!/bin/sh

echo "[Init] Running Migrations..."
/app/artisan migrate --force

echo "[Init] Clearing old caches..."
/app/artisan optimize:clear

echo "[Init] Seeding Database..."
/app/artisan db:seed --force

echo "[Init] Optimize..."
/app/artisan optimize

chown -R www-data:www-data /app/storage /app/bootstrap/cache
chmod -R 775 /app/storage /app/bootstrap/cache

echo "[Init] Restarting Queue Signal..."
/app/artisan queue:restart

echo "[Init] Done."
