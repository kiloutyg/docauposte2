#!/bin/bash

# Move EnvVars to project dir
cp /etc/ssl/my-certs/.env /var/www/

# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Caching dotenv variable
composer dump-env prod

# Install JavaScript dependencies (with retries to work around transient DNS/network errors)
MAX_RETRIES=5
RETRY_DELAY=5
COUNT=0
until [ $COUNT -ge $MAX_RETRIES ]
do
  yarn install --ignore-scripts --production && break
  COUNT=$((COUNT+1))
  echo "yarn install failed, retry $COUNT/$MAX_RETRIES in ${RETRY_DELAY}s..."
  sleep $RETRY_DELAY
done
if [ $COUNT -ge $MAX_RETRIES ]; then
  echo "yarn install failed after $MAX_RETRIES attempts"
  exit 1
fi

# Clear Composer cache
composer clear-cache

# Generate migration files and apply them
php bin/console doctrine:migrations:diff --no-interaction;
for version in $(php bin/console doctrine:migrations:status --no-interaction | grep "Latest" | awk '{print $5}'); do
  php bin/console doctrine:migrations:execute --up "$version" --no-interaction;
done

# Clear and warm up Symfony cache
php ./bin/console cache:clear --no-warmup --env=prod
php ./bin/console cache:warmup --env=prod

# Build assets with Webpack Encore
# yarn encore prod
yarn build

# Start Apache in the foreground
exec apache2-foreground
