#!/bin/sh
mkdir -p ./public/doc 
export http_proxy='http://10.0.0.1:80'
composer require symfony/maker-bundle / symfony/orm-pack  symfony/asset symfony/apache-pack symfony/error-handler symfony/templating symfony/ux-turbo symfony/profiler-pack symfony/var-dumper symfony/webpack-encore-bundle 
yarn add bootstrap jquery @popperjs/core sass-loader  sass @fontsource/roboto-condensed @fortawesome/fontawesome-free axios core-js webpack encore webpack-cli webpack-notifier @symfony/webpack-encore favicon
composer install
composer update 
yarn install
yarn upgrade
composer clear-cache
chmod 777 . -R -v
# yarn encore production &
php bin/console make:migration
php bin/console doctrine:migrations:migrate 

exec apache2-foreground  &
# yarn watch &
yarn encore dev --watch