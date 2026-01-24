#!/bin/bash

./vendor/bin/sail down

echo "Deleting stuff that may be broken..."
rm -rf ./vendor
rm -rf ./node_modules
rm ./bootstrap/cache/packages.php
rm ./bootstrap/cache/services.php
rm ./frankenphp
rm ./.phpstorm.meta.php
rm ./_ide_helper.php
rm ./public/hot
rm ./public/frankenphp-worker.php
rm -rf ./public/css/filament
rm -rf ./public/fonts/filament
rm -rf ./public/js/filament
rm -rf ./resources/js/actions
rm -rf ./resources/js/routes
rm -rf ./resources/js/wayfinder
rm -rf ./public/build

echo "Installing Composer dependencies..."
docker run --rm \
    --interactive \
    --tty \
    --volume "$PWD":/app \
    --user "$(id -u):$(id -g)" \
    composer install --ignore-platform-reqs

#echo "Building Sail (can take a while)..."
#./vendor/bin/sail build --no-cache

echo "Starting Sail..."
./vendor/bin/sail up -d

until ./vendor/bin/sail ps | grep "laravel.test" | grep -q "(healthy)"; do
    echo "waiting for sail to be healthy..."
    sleep 3
done

echo "(re)Installing dependencies using sail..."

./vendor/bin/sail composer install
./vendor/bin/sail npm install

echo "Resetting stuff..."
./vendor/bin/sail artisan optimize:clear
./vendor/bin/sail artisan migrate:fresh --seed
./vendor/bin/sail composer helper
./vendor/bin/sail down

echo "everything should be fixed now"
