#!/bin/sh

# Install the app dependencies 
composer install;
composer update ;
yarn install;
yarn upgrade;
composer clear-cache;

# Set the permissions
chmod 777 . -R;

# Clear the cache
php bin/console cache:clear --no-warmup --env=dev;

# Warm up the cache
php bin/console cache:warmup --env=dev;

# Remove old migrations folder and files
rm -rf migrations;

# Create the migrations directory
mkdir -p migrations;

# Create the database and run the migrations
php bin/console make:migration;
php bin/console doctrine:migrations:migrate;

# Build the assets and start the server
exec apache2-foreground &
yarn encore dev --watch

