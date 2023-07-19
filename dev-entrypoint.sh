#!/bin/sh

# Install the app dependencies 
export http_proxy='http://10.0.0.1:80';
composer install;
composer update ;
yarn install;
yarn upgrade;
composer clear-cache;

# Set the permissions
chmod 777 . -R -v;

# Clear the cache
php bin/console cache:clear --no-warmup --env=dev;

# Warm up the cache
php bin/console cache:warmup --env=dev;

# Create the database and run the migrations
php bin/console make:migration;
php bin/console doctrine:migrations:migrate;

# Build the assets and start the server
exec apache2-foreground &
yarn encore dev --watch
