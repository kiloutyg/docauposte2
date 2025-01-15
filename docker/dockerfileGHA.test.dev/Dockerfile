FROM php:8.2-apache-bookworm AS base

# Create a non-root user and group
RUN addgroup --system nonroot \
  && adduser --system --ingroup nonroot nonroot
# RUN addgroup nonroot \
#   && adduser --ingroup nonroot nonroot

# Set working directory
WORKDIR /var/www

# Copy custom php.ini
COPY ./docker/dockerfileGHA.test.dev/php.ini /usr/local/etc/php/php.ini

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
RUN rm -rf html

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

USER nonroot

COPY --chown=nonroot:nonroot --chmod=644 ./docker/dockerfileGHA.test.dev/composer.sh .
RUN ls -la
RUN cat composer.sh
# RUN chmod +x composer.sh \
#   && /usr/bin/bash composer.sh \
#   && mv composer.phar /usr/local/bin/composer

RUN chmod +x composer.sh 
RUN ./composer.sh 
RUN mv composer.phar /usr/local/bin/composer

# Install Symfony CLI
RUN curl --tlsv1.2 -sS https://get.symfony.com/cli/installer | bash \
  && mv /root/.symfony5/bin/symfony /usr/local/bin/symfony




# Building stage
FROM base AS building

# Ensure we're using the non-root user
RUN ls -la

RUN chmod 644 /var/www -R

COPY --chown=nonroot:nonroot --chmod=644 . .
RUN ls -la
# Switch to non-root user for subsequent operations
USER nonroot
RUN cat composer.json

# Install dependencies and perform build steps
RUN composer install \
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
