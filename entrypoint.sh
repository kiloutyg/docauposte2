#!/bin/sh

composer require asset && /
composer require symfony/apache-pack &&
composer require --dev symfony/profiler-pack && 
composer require debug && 
composer require templates && 
composer require symfony/ux-turbo && 
yarn add bootstrap --dev && yarn add jquery @popperjs/core --dev && 
yarn add @fontsource/roboto-condensed --dev && 
yarn add @fortawesome/fontawesome-free --dev && 
yarn add axios --dev &&
composer install && 
yarn install &&
chmod 777 .