# Dockerfile: docker/dockerfileGHA.test.dev/Dockerfile

# Use the official PHP 8.2 with Apache on Debian Bookworm as the base image
FROM php:8.2-apache-bookworm AS base

# Set the working directory inside the container
WORKDIR /var/www

# Copy custom php.ini configuration into the container
COPY ./docker/dockerfileGHA.dev/php.ini /usr/local/etc/php/php.ini

# Configure system settings and install necessary packages and extensions
RUN \
  # Set the timezone to Europe/Paris
  ln -sf /usr/share/zoneinfo/Europe/Paris /etc/localtime \
  # Enable Apache mod_rewrite module
  && a2enmod rewrite \
  # Restart Apache to apply changes
  && service apache2 restart \
  # Update package lists
  && apt update -y \
  # Upgrade installed packages
  && apt upgrade -y \
  # Install required packages without recommendations
  && apt install --no-install-recommends -y \
  ca-certificates \
  curl \
  git \
  gnupg \
  libmagickwand-dev \
  libxslt-dev \
  libzip-dev \
  wget \
  # Install PHP extensions
  && docker-php-ext-install \
  bcmath \
  gd \
  mysqli \
  opcache \
  pdo \
  pdo_mysql \
  xsl \
  zip \
  # Install PECL extensions
  && pecl install \
  imagick \
  xdebug \
  # Enable installed PHP extensions
  && docker-php-ext-enable \
  gd \
  imagick \
  mysqli \
  opcache \
  pdo \
  pdo_mysql \
  xsl \
  xdebug \
  zip \
  # Set global Git configuration
  && git config --global user.email "floriandkhissi@gmail.com" \
  && git config --global user.name "Florian Dkhissi" \
  # Mark /var/www as a safe Git directory
  && git config --global --add safe.directory /var/www \
  # Change Apache document root to /var/www/public
  && sed -i -e "s/\/var\/www\/html/\/var\/www\/public/g" /etc/apache2/sites-available/000-default.conf \
  # Create directory for APT keyrings
  && mkdir -p /etc/apt/keyrings \
  # Download NodeSource GPG key and add Node.js repository
  && curl --tlsv1.2 -fsS https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | \
  gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg \
  && echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_23.x nodistro main" | \
  tee /etc/apt/sources.list.d/nodesource.list \
  # Update package lists after adding Node.js repository
  && apt update -y \
  # Install Node.js
  && apt install --no-install-recommends -y nodejs \
  # Update npm to version 11 globally
  && npm install --ignore-scripts -g npm@11 \
  # Install Yarn package manager globally
  && npm install --ignore-scripts -g yarn \
  # Clean up APT cache and remove residual files to reduce image size
  && apt clean \
  && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Copy the Composer installation script into the container
COPY ./docker/dockerfileGHA.dev/composer.sh /usr/local/bin/composer.sh

# Install Composer and Symfony CLI
RUN \
  # Run Composer installation script
  /usr/local/bin/composer.sh \
  # Move Composer to a global location
  && mv composer.phar /usr/local/bin/composer \
  # Remove the Composer installation script
  && rm /usr/local/bin/composer.sh \
  # Download and install Symfony CLI
  && curl --tlsv1.2 -sS https://get.symfony.com/cli/installer | bash \
  # Move Symfony CLI to a global location
  && mv /root/.symfony5/bin/symfony /usr/local/bin/symfony