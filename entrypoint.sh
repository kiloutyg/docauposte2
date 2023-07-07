#!/bin/sh

export http_proxy='http://10.0.0.1:80';
# composer require --dev symfony/asset symfony/apache-pack symfony/error-handler symfony/templating symfony/ux-turbo symfony/profiler-pack symfony/var-dumper symfony/webpack-encore-bundle  symfony/ux-turbo;
# composer require symfony/asset symfony/apache-pack symfony/error-handler symfony/templating symfony/ux-turbo symfony/profiler-pack symfony/var-dumper symfony/webpack-encore-bundle  symfony/ux-turbo;
# yarn add bootstrap jquery @popperjs/core sass-loader  sass @fontsource/roboto-condensed @fortawesome/fontawesome-free axios core-js webpack encore webpack-cli webpack-notifier @symfony/webpack-encore favicon --dev;
# yarn add bootstrap jquery @popperjs/core sass-loader  sass @fontsource/roboto-condensed @fortawesome/fontawesome-free axios core-js webpack encore webpack-cli webpack-notifier @symfony/webpack-encore favicon;
composer install --no-dev --optimize-autoloader;

#composer install;
#composer update ;
yarn install --production;
#yarn upgrade;
composer clear-cache;
chmod 777 . -R -v;
# yarn encore production &
php bin/console cache:clear --no-warmup --env=prod;
php bin/console cache:warmup --env=prod;
php bin/console make:migration;
php bin/console doctrine:migrations:migrate;

#exec apache2-foreground &
# yarn watch &
#yarn encore dev --watch
yarn run production --progress;
exec apache2-foreground;
