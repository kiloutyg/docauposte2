FROM php:8.1-rc-alpine3.17

RUN apk add apache2 php81-apache2 openrc php81 bash

RUN apk add  curl libxslt-dev libzip-dev git wget imagemagick-dev 
RUN docker-php-ext-install pdo mysqli pdo_mysql zip opcache xsl
RUN pecl install xdebug imagick \
    && docker-php-ext-enable imagick xdebug mysqli pdo_mysql pdo zip opcache xsl

RUN rc-service apache2 start
RUN rc-update add apache2

# fix git author
RUN git config --global user.email "symfo@symfo"
RUN git config --global user.name "symfo"
RUN git config --global --add safe.directory /var/www

RUN docker-php-ext-install pdo mysqli pdo_mysql zip opcache xsl
RUN pecl install xdebug imagick \
    && docker-php-ext-enable imagick xdebug mysqli pdo_mysql pdo zip opcache xsl

# set default vhost to target /symfony/app/public
WORKDIR /var/www
RUN sed -i -e "s/\/var\/www\/html/\/var\/www\/public/g" /etc/apache2/sites-available/000-default.conf

# install nodejs 
RUN curl -sL https://apk.nodesource.com/setup_16.x | bash -
RUN apk add nodejs
 
RUN npm install -g yarn

# install composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php -r "if (hash_file('sha384', 'composer-setup.php') === '55ce33d7678c5a611085589f1f3ddf8b3c52d662cd01d4ba75c0ee0459970c2200a51f492d557530c71c15d8dba01eae') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
RUN php composer-setup.php
RUN php -r "unlink('composer-setup.php');"
RUN mv composer.phar /usr/bin/composer && chmod +x /usr/bin/composer

# install symfony cli
RUN curl -sS https://get.symfony.com/cli/installer | bash \
    && mv /root/.symfony5/bin/symfony /usr/local/bin/symfony