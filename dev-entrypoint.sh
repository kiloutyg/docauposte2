#!/bin/sh

# Install the app dependencies 
composer install;

composer dump-env dev

yarn install;
composer clear-cache;

# Clear the cache
php bin/console cache:clear --no-warmup --env=dev;

# Warm up the cache
php bin/console cache:warmup --env=dev;

# Set the permissions
chmod 755 . -R;

# # Set the permissions
# chown -R www-data:www-data /var/www/var/;

# chmod 777 /var/www/var/ -R;

# Create the migrations directory
# mkdir -p migrations;

# Create the database and run the migrations
php bin/console make:migration;
php bin/console doctrine:migrations:migrate;

# Build the assets and start the server
exec apache2-foreground &
yarn encore dev --watch