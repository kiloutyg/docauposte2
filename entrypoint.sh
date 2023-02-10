#!/bin/sh

composer require asset symfony/apache-pack debug templates symfony/ux-turbo symfony/profiler-pack --dev
yarn add bootstrap jquery @popperjs/core @fontsource/roboto-condensed @fortawesome/fontawesome-free axios core-js webpack encore webpack-cli webpack-notifier @symfony/webpack-encore --dev
composer install 
yarn install
composer clear-cache
chmod 777 . 
# yarn watch &
# yarn encore production &
export HTTP_PROXY=http://10.0.0.1:80
exec apache2-foreground 