FROM php:8.3-apache-bookworm AS base

RUN addgroup -S nonroot \
  && adduser -S nonroot -G nonroot

USER nonroot

COPY php.ini /usr/local/etc/php/php.ini
RUN a2enmod rewrite \
  && service apache2 restart
RUN apt-get update -y \
  && apt-get install --no-install-recommends -y ca-certificates curl libxslt-dev libzip-dev git wget gnupg \
  && apt-get install --no-install-recommends -y libmagickwand-dev \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*
RUN docker-php-ext-install pdo mysqli pdo_mysql zip opcache xsl gd bcmath \
  && pecl install xdebug imagick\
  && docker-php-ext-enable xdebug mysqli pdo_mysql pdo zip opcache xsl imagick gd


# fix git author
RUN git config --global user.email "floriandkhissi@gmail.com"
RUN git config --global user.name "Florian Dkhissi"
RUN git config --global --add safe.directory /var/www

# set default vhost to target /symfony/app/public
RUN sed -i -e "s/\/var\/www\/html/\/var\/www\/public/g" /etc/apache2/sites-available/000-default.conf

# install nodejs 
RUN mkdir -p /etc/apt/keyrings
RUN curl --tlsv1.2 -fsS https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg
RUN echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_23.x nodistro main" | tee /etc/apt/sources.list.d/nodesource.list
RUN apt-get update -y \
  && apt-get install --no-install-recommends -y nodejs \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*
RUN npm install --ignore-scripts -g npm@11
RUN npm install --ignore-scripts -g yarn

# install composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php -r "if (hash_file('sha384', 'composer-setup.php') === 'dac665fdc30fdd8ec78b38b9800061b4150413ff2e3b6f88543c636f7cd84f6db9189d43a81e5503cda447da73c7e5b6') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
RUN php composer-setup.php
RUN php -r "unlink('composer-setup.php');"
RUN mv composer.phar /usr/bin/composer && chmod +x /usr/bin/composer

# install symfony cli
RUN curl --tlsv1.2 -sS https://get.symfony.com/cli/installer | bash \
  && mv /root/.symfony5/bin/symfony /usr/local/bin/symfony
WORKDIR /var/www


# Building stage
FROM base AS building

USER nonroot

COPY . .

# Install dependencies and perform build steps
RUN composer install\
  && yarn install --ignore-scripts \
  && composer clear-cache \
  # Clear the cache
  && php bin/console cache:clear --no-warmup --env=dev \
  # Warm up the cache
  && php bin/console cache:warmup --env=dev \
  && chmod 644 . -R \
  # Remove old migrations folder and files
  && rm -rf migrations\
  # Create the migrations directory
  && mkdir -p migrations\
  # Create the database and run the migrations
  && php bin/console make:migration\
  && php bin/console doctrine:migrations:migrate\
  # Build the assets and start the server
  && yarn encore dev --watch

