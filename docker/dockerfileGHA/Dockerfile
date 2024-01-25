FROM php:8.2-apache-bookworm

COPY php.ini /usr/local/etc/php/php.ini
RUN a2enmod rewrite \
  && service apache2 restart
RUN apt-get update -y \
  && apt-get install -y ca-certificates curl libxslt-dev libzip-dev git wget gnupg --no-install-recommends \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*
RUN docker-php-ext-install pdo mysqli pdo_mysql zip opcache xsl \
  && pecl install xdebug \
  && docker-php-ext-enable xdebug mysqli pdo_mysql pdo zip opcache xsl

# fix git author
RUN git config --global user.email "floriandkhissi@gmail.com"
RUN git config --global user.name "Florian Dkhissi"
RUN git config --global --add safe.directory /var/www

# set default vhost to target /symfony/app/public
WORKDIR /var/www
RUN sed -i -e "s/\/var\/www\/html/\/var\/www\/public/g" /etc/apache2/sites-available/000-default.conf

# install nodejs 
RUN mkdir -p /etc/apt/keyrings
RUN curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg
RUN echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_20.x nodistro main" | tee /etc/apt/sources.list.d/nodesource.list
RUN apt-get update -y \
  && apt-get install -y nodejs \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*
RUN npm install -g yarn

# install composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php -r "if (hash_file('sha384', 'composer-setup.php') === 'e21205b207c3ff031906575712edab6f13eb0b361f2085f1f1237b7126d785e826a450292b6cfd1d64d92e6563bbde02') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
RUN php composer-setup.php
RUN php -r "unlink('composer-setup.php');"
RUN mv composer.phar /usr/bin/composer && chmod +x /usr/bin/composer

# install symfony cli
RUN curl -sS https://get.symfony.com/cli/installer | bash \
  && mv /root/.symfony5/bin/symfony /usr/local/bin/symfony
