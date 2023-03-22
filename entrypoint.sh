#!/bin/sh
mkdir -p ./public/doc 
export http_proxy='http://10.0.0.1:80'
composer require asset symfony/apache-pack debug templates symfony/ux-turbo symfony/profiler-pack symfony/var-dumper --dev
yarn add bootstrap jquery @popperjs/core @fontsource/roboto-condensed @fortawesome/fontawesome-free axios core-js webpack encore webpack-cli webpack-notifier @symfony/webpack-encore --dev
composer install 
yarn install
composer clear-cache
chmod 777 . 
# yarn encore production &

exec apache2-foreground  &
yarn watch