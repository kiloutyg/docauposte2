#!/bin/sh

# Install the app dependencies 
composer install --dev;

composer dump-env dev

yarn install;
composer clear-cache;

# Clear the cache
php bin/console cache:clear --no-warmup --env=dev;

# Warm up the cache
php bin/console cache:warmup --env=dev;

# Set the ownership
chown -R www-data:www-data /var/www/var/;
chown -R www-data:www-data /var/www/public/;
chown -R www-data:www-data /var/www/migrations/;

# Set the permissions
chmod 755 . -R;

# Create the database and run the migrations
php bin/console make:migration;
php bin/console doctrine:migrations:migrate;

# Build the assets and start the server
exec apache2-foreground &
yarn encore dev --watch