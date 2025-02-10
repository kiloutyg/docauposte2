#!/bin/bash

# Move EnvVars to project dir
cp /etc/ssl/my-certs/.env /var/www/

# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Caching dotenv variable
composer dump-env prod

# Install JavaScript dependencies
yarn install --ignore-scripts --production

# Clear Composer cache
composer clear-cache

# Run the migrations
php bin/console doctrine:migrations:migrate;

# Clear and warm up Symfony cache
php ./bin/console cache:clear --no-warmup --env=prod
php ./bin/console cache:warmup --env=prod

# Build assets with Webpack Encore
yarn encore prod

# Start Apache in the foreground
exec apache2-foreground
