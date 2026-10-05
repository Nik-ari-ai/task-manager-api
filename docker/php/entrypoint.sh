#!/bin/bash

set -e

cd /var/www/app

if [ ! -d "vendor" ]; then
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

until php bin/console doctrine:query:sql "SELECT 1" > /dev/null 2>&1; do
    echo "Waiting for the database to become available..."
    sleep 2
done

php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

exec "$@"
