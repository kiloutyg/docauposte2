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
COPY --chown=nonroot:nonroot --chmod=755 ./docker/dockerfileGHA.test.dev/composer.sh /usr/local/bin/composer.sh

# Install Composer and Symfony CLI
RUN /usr/local/bin/composer.sh \
  && mv composer.phar /usr/local/bin/composer \
  && rm /usr/local/bin/composer.sh \
  && curl --tlsv1.2 -sS https://get.symfony.com/cli/installer | bash \
  && mv /root/.symfony5/bin/symfony /usr/local/bin/symfony \
  && chown -R nonroot:nonroot /var/www

# Switch to non-root user
USER nonroot

# Building stage
FROM base AS building

# Switch to non-root user
USER nonroot

# Set working directory
WORKDIR /var/www

# Copy application code with correct ownership
COPY --chown=nonroot:nonroot --chmod=755 . /var/www

# Install dependencies and perform build steps
RUN  cp .env.test .env \
  && composer install \
  && yarn install --ignore-scripts \
  && composer clear-cache \
  && php ./bin/console cache:clear --no-warmup --env=prod \
  && php ./bin/console cache:warmup --env=prod \
  && yarn encore prod \
  && rm -rf .env node_modules vendor