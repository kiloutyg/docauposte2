# Dockerfile: docker/dockerfileGHA.test.dev/Dockerfile

FROM php:8.2-apache-bookworm AS base

# Create a non-root user and group
RUN addgroup --system nonroot \
  && adduser --system --ingroup nonroot nonroot

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
  libxslt-dev \
  libzip-dev \
  git \
  wget \
  gnupg \
  libmagickwand-dev 

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

# Set global git configuration and adjust Apache configuration
RUN git config --global user.email "floriandkhissi@gmail.com" \
  && git config --global user.name "Florian Dkhissi" \
  && git config --global --add safe.directory /var/www \
  && sed -i -e "s/\/var\/www\/html/\/var\/www\/public/g" /etc/apache2/sites-available/000-default.conf

# Install Node.js
RUN mkdir -p /etc/apt/keyrings \
  && curl --tlsv1.2 -fsS https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | \
  gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg \
  && echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_23.x nodistro main" | \
  tee /etc/apt/sources.list.d/nodesource.list \
  && apt update -y \
  && apt install -y nodejs \
  && npm install --ignore-scripts -g npm@11 \
  && npm install --ignore-scripts -g yarn \
  && apt clean \
  && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Copy composer.sh script
COPY ./docker/dockerfileGHA.test.dev/composer.sh /usr/local/bin/composer.sh

# Set execute permission for composer.sh
RUN chmod +x /usr/local/bin/composer.sh

# Install Composer and Symfony CLI
RUN /usr/local/bin/composer.sh \
  && mv composer.phar /usr/local/bin/composer \
  && curl --tlsv1.2 -sS https://get.symfony.com/cli/installer | bash \
  && mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

# Change ownership of the working directory
RUN chown -R nonroot:nonroot /var/www

# Switch to non-root user
USER nonroot

# Building stage
FROM base AS building

# Set working directory
WORKDIR /var/www

# Copy application code with correct ownership
COPY --chown=nonroot:nonroot . /var/www

# Install dependencies and perform build steps
RUN composer install \
  && yarn install --ignore-scripts \
  && composer clear-cache \
  && php ./bin/console cache:clear --no-warmup --env=dev \
  && php ./bin/console cache:warmup --env=dev \
  && chmod -R u+rwX,go=rX /var/www \
  && rm -rf migrations \
  && mkdir -p migrations \
  && php ./bin/console make:migration \
  && php ./bin/console doctrine:migrations:migrate \
  && yarn encore dev --watch