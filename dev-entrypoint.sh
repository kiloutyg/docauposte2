#!/bin/sh

export http_proxy='http://10.0.0.1:80';
composer install;
composer update ;
yarn install;
yarn upgrade;
composer clear-cache;
chmod 777 . -R -v;
php bin/console cache:clear --no-warmup --env=prod;
php bin/console cache:warmup --env=prod;
php bin/console make:migration;
php bin/console doctrine:migrations:migrate;

exec apache2-foreground &
yarn encore dev --watch

