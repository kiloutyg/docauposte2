#!/bin/sh
mkdir -p ./public/doc 
export http_proxy='http://10.0.0.1:80'
composer require --no-dev    symfony/maker-bundle \
                        symfony/orm-pack \
                        symfony/asset \
                        symfony/apache-pack \
                        symfony/error-handler \
                        symfony/templating \
                        symfony/ux-turbo \
                        symfony/profiler-pack \
                        symfony/var-dumper \
                        symfony/webpack-encore-bundle \
                        doctrine/annotations \
                        doctrine/doctrine-bundle \
                        doctrine/doctrine-migrations-bundle \
                        doctrine/orm \
                        phpdocumentor/reflection-docblock \
                        phpstan/phpdoc-parser \
                        symfony/apache-pack \
                        symfony/asset \
                        symfony/console \
                        symfony/doctrine-messenger \
                        symfony/dotenv \
                        symfony/error-handler \
                        symfony/expression-language \
                        symfony/flex \
                        symfony/form \
                        symfony/framework-bundle \
                        symfony/http-client \
                        symfony/http-foundation \
                        symfony/intl \
                        symfony/mailer \
                        symfony/mime \
                        symfony/monolog-bundle \
                        symfony/notifier \
                        symfony/process \
                        symfony/property-access \
                        symfony/property-info \
                        symfony/runtime \
                        symfony/security-bundle \
                        symfony/serializer \
                        symfony/string \
                        symfony/templating \
                        symfony/translation \
                        symfony/twig-bundle \
                        symfony/ux-turbo \
                        symfony/validator \
                        symfony/var-dumper \
                        symfony/web-link \
                        symfony/webpack-encore-bundle \
                        symfony/yaml \
                        twig/extra-bundle \
                        twig/twig \
                        doctrine/doctrine-fixtures-bundle \
                        phpunit/phpunit \
                        symfony/browser-kit \
                        symfony/css-selector \
                        symfony/debug-bundle \
                        symfony/maker-bundle \
                        symfony/phpunit-bridge \
                        symfony/stopwatch \
                        symfony/web-profiler-bundle
        

yarn add  bootstrap \
                jquery \
                @popperjs/core \
                sass-loader \
                sass \
                @fontsource/roboto-condensed \
                @fortawesome/fontawesome-free \
                axios \
                core-js \
                webpack \
                encore \
                webpack-cli \
                webpack-notifier \
                @symfony/webpack-encore \
                @symfony/ux-turbo \
                @symfony/stimulus-bridge \
                @symfony/stimulus-bundle \
                regenerator-runtime \
                favicon \
                @babel/core \
                @babel/preset-env \
                @hotwired/turbo \
                @hotwired/stimulus \
                node-sass
                

composer install
composer update 
yarn install
yarn upgrade
composer clear-cache
chmod 777 . -R -v
php bin/console make:migration
php bin/console doctrine:migrations:migrate 

exec apache2-foreground  &
yarn encore production 
