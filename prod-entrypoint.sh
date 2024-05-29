#!/bin/sh

# Entrypoint script destined to the production environment

# Install the app dependencies
composer install --no-dev --optimize-autoloader
yarn install --production
composer clear-cache

# Clear and warm up the cache
php bin/console cache:clear --no-warmup --env=prod
php bin/console cache:warmup --env=prod


# Set the permissions 
chmod -R 777 /var/www/var/cache/prod/pools
chown -R www-data:www-data /var/www/var/cache/prod

chmod -R 777 .

# Build the assets
yarn run encore production --progress

# Start the server
exec apache2-foreground