#!/bin/sh

composer require asset symfony/apache-pack debug templates symfony/ux-turbo
composer require --dev symfony/profiler-pack 

yarn add bootstrap jquery @popperjs/core @fontsource/roboto-condensed @fortawesome/fontawesome-free axios --dev

composer install 
yarn install

composer clear-cache

chmod 777 . 
yarn watch &
exec apache2-foreground &