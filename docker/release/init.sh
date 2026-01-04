#!/usr/bin/env bash

echo "[Init] Clearing old caches..."
php /app/artisan optimize:clear

echo "[Init] Running Migrations..."
php /app/artisan migrate --force

echo "[Init] Seeding Database..."
php /app/artisan db:seed --force

echo "[Init] Caching Configuration..."
php /app/artisan config:cache

echo "[Init] Caching Events..."
php /app/artisan event:cache

echo "[Init] Caching Routes..."
php /app/artisan route:cache

echo "[Init] Caching Views..."
php /app/artisan view:cache

echo "[Init] Optimize..."
php /app/artisan optimize

echo "[Init] Restarting Queue Signal..."
php /app/artisan queue:restart

echo "[Init] Done."
