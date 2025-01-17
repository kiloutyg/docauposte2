# Dockerfile: docker/dockerfileGHA.test.dev/Dockerfile

FROM php:8.2-apache-bookworm AS base

# Set working directory
WORKDIR /var/www

# Copy custom php.ini
COPY ./docker/dockerfileGHA.test.dev/php.ini /usr/local/etc/php/php.ini

# Run commands that require root privileges
RUN a2enmod rewrite \
  && service apache2 restart

RUN apt update -y \
  && apt upgrade -y \
  && apt install --no-install-recommends -y \
  ca-certificates \
  curl \
  git \
  gnupg \
  libmagickwand-dev \
  libxslt-dev \
  libzip-dev \
  wget \
  && docker-php-ext-install \
  bcmath \
  gd \
  mysqli \
  opcache \
  pdo \
  pdo_mysql \
  xsl \
  zip\
  && pecl install \
  imagick \
  xdebug \
  && docker-php-ext-enable \
  gd \
  imagick \
  mysqli \
  opcache \
  pdo \
  pdo_mysql \
  xsl \
  xdebug \
  zip 

# Set global git configuration and adjust Apache configuration
RUN git config --global user.email "floriandkhissi@gmail.com" \
  && git config --global user.name "Florian Dkhissi" \
  && git config --global --add safe.directory /var/www \
  && sed -i -e "s/\/var\/www\/html/\/var\/www\/public/g" /etc/apache2/sites-available/000-default.conf \
  && mkdir -p /etc/apt/keyrings \
  && curl --tlsv1.2 -fsS https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | \
  gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg \
  && echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_23.x nodistro main" | \
  tee /etc/apt/sources.list.d/nodesource.list \
  && apt update -y \
  && apt install --no-install-recommends -y nodejs \
  && npm install --ignore-scripts -g npm@11 \
  && npm install --ignore-scripts -g yarn \
  && apt clean \
  && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Copy composer.sh script
COPY ./docker/dockerfileGHA.test.dev/composer.sh /usr/local/bin/composer.sh

# Install Composer and Symfony CLI
RUN /usr/local/bin/composer.sh \
  && mv composer.phar /usr/local/bin/composer \
  && rm /usr/local/bin/composer.sh \
  && curl --tlsv1.2 -sS https://get.symfony.com/cli/installer | bash \
  && mv /root/.symfony5/bin/symfony /usr/local/bin/symfony