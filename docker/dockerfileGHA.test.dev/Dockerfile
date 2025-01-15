FROM php:8.2-apache-bookworm AS base

# Create a non-root user and group
RUN addgroup --system nonroot \
  && adduser --system --ingroup nonroot nonroot

# Copy custom php.ini
COPY php.ini /usr/local/etc/php/php.ini

# Run commands that require root privileges
RUN a2enmod rewrite \
  && service apache2 restart

RUN apt-get update -y \
  && apt-get upgrade -y \
  && apt-get install --no-install-recommends -y \
  ca-certificates \
  curl \
  libxslt-dev \
  libzip-dev \
  git \
  wget \
  gnupg \
  libmagickwand-dev \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN docker-php-ext-install \
  pdo \
  mysqli \
  pdo_mysql \
  zip \
  opcache \
  xsl \
  gd \
  bcmath

RUN pecl install xdebug \
  imagick

RUN docker-php-ext-enable \
  xdebug \
  mysqli \
  pdo_mysql \
  pdo \
  zip \
  opcache \
  xsl \
  imagick \
  gd

# Set global git configuration
RUN git config --global user.email "floriandkhissi@gmail.com" \
  && git config --global user.name "Florian Dkhissi" \
  && git config --global --add safe.directory /var/www

# Set default vhost to target /symfony/app/public
RUN sed -i -e "s/\/var\/www\/html/\/var\/www\/public/g" /etc/apache2/sites-available/000-default.conf

# Install Node.js
RUN mkdir -p /etc/apt/keyrings \
  && curl --tlsv1.2 -fsS https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | \
  gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg \
  && echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_23.x nodistro main" | \
  tee /etc/apt/sources.list.d/nodesource.list \
  && apt-get update -y \
  && apt-get install --no-install-recommends -y nodejs \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN npm install --ignore-scripts -g npm@11 \
  && npm install --ignore-scripts -g yarn

COPY composer.sh composer.sh
RUN chmod +x composer.sh \
  && /usr/bin/bash composer.sh \
  && mv composer.phar /usr/local/bin/composer

# Install Composer
# RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
#   && php -r "if (hash_file('sha384', 'dac665fdc30fdd8ec78b38b9800061b4150413ff2e3b6f88543c636f7cd84f6db9189d43a81e5503cda447da73c7e5b6') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" \
#   && php composer-setup.php \
#   && php -r "unlink('composer-setup.php');" \
#   && mv composer.phar /usr/bin/composer \
#   && chmod +x /usr/bin/composer

# Install Symfony CLI
RUN curl --tlsv1.2 -sS https://get.symfony.com/cli/installer | bash \
  && mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

# Set working directory
WORKDIR /var/www


# Switch to non-root user for subsequent operations
USER nonroot

# Building stage
FROM base AS building

# Ensure we're using the non-root user
USER nonroot

COPY . .

# Install dependencies and perform build steps
RUN /usr/local/bin/composer install \
  && yarn install --ignore-scripts \
  && composer clear-cache \
  # Clear the cache
  && php bin/console cache:clear --no-warmup --env=dev \
  # Warm up the cache
  && php bin/console cache:warmup --env=dev \
  # Set permissions
  && chmod 644 . -R \
  # Remove old migrations folder and files
  && rm -rf migrations \
  # Create the migrations directory
  && mkdir -p migrations \
  # Create the database and run the migrations
  && php bin/console make:migration \
  && php bin/console doctrine:migrations:migrate \
  # Build the assets
  && yarn encore dev --watch
