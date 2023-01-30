FROM php:8.1-rc-apache-buster

# Install composer 
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install git
RUN apt-get update && apt-get install -y git zlib1g-dev libzip-dev && docker-php-ext-install zip

# Install php extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

