#!/bin/bash
set -e

if [ ! -d "vendor" ]; then
    composer install --no-progress --no-interaction
fi

sleep 10

php artisan migrate --force

exec "$@"
