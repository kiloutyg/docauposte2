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


# Still seems better to run the migrations manually
# Run the migrations
# set -e
# echo "Generating diff doctrine migration script..."
# php bin/console doctrine:migrations:diff --no-interaction;
# 
# echo "🔎 Listing available migration files..."
# available_versions=$(ls migrations/Version*.php | sed -E 's/.*Version([0-9]+)\.php/\1/')
# 
# echo "🔍 Getting executed versions from DB..."
# executed_versions=$(php bin/console doctrine:query:sql "SELECT version FROM doctrine_migration_versions" 2>/dev/null | grep -Eo '[0-9]{14}')
# 
# echo "🚀 Starting per-version execution..."
# for version in $available_versions; do
#   if ! echo "$executed_versions" | grep -q "$version"; then
#     echo "➡️  Running migration $version"
#     if php bin/console doctrine:migrations:execute --up DoctrineMigrations\\Version"$version" --no-interaction; then
#       echo "✅ Successfully executed $version"
#     else
#       echo "⚠️  Failed to execute $version, marking as executed"
#       php bin/console doctrine:migrations:version DoctrineMigrations\\Version"$version" --add --no-interaction
#     fi
#   fi
# done
# 
# echo "✅ All applicable migrations processed."

php bin/console doctrine:migrations:diff --no-interaction;
for version in $(php bin/console doctrine:migrations:status --no-interaction | grep "Latest" | awk '{print $5}'); do
  php bin/console doctrine:migrations:execute --up "$version" --no-interaction;
done
# Clear and warm up Symfony cache
php ./bin/console cache:clear --no-warmup --env=prod
php ./bin/console cache:warmup --env=prod

# Build assets with Webpack Encore
yarn encore prod

# Start Apache in the foreground
exec apache2-foreground
