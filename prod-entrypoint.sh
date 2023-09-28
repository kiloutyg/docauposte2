#!/bin/sh

# Entrypoint scritp destined to the production environment
# Install the app dependencies 
composer install --no-dev --optimize-autoloader;
yarn install --production;
composer clear-cache;

# Set the permissions and clear the cache
chmod 777 . -R ;
php bin/console cache:clear --no-warmup --env=prod;

# Warm up the cache
php bin/console cache:warmup --env=prod;

# # Create the migrations directory
# mkdir -p migrations;

# # Create the database and run the migrations
# php bin/console make:migration;
# php bin/console doctrine:migrations:migrate;

# Build the assets
yarn run encore production --progress;

# Start the server
exec apache2-foreground;