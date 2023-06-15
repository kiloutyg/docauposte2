#!/bin/bash




# Prompt for database details
read -p "Please enter your MySQL username: " db_username
read -p "Please enter your MySQL password: " db_password
read -p "Please enter your database name: " db_name
# read -p "Please enter your server version (e.g. mariadb-10.4.13): " MariaDB- . server_version

# Generate a random secret
app_secret=$(openssl rand -hex 16)
# DATABASE_URL=mysql://\${MYSQL_USER}:\${MYSQL_PASSWORD}@\${DB_HOST}/\${MYSQL_DATABASE}?serverVersion=${server_version}

# Create .env file
cat > .env <<EOL
# .env

APP_ENV=prod
APP_SECRET=${app_secret}
SYMFONY_DEPRECATIONS_HELPER=weak

MESSENGER_TRANSPORT_DSN=doctrine://default?auto_setup=0

# MySQL settings
MYSQL_ROOT_PASSWORD=$(openssl rand -hex 8)
MYSQL_DATABASE=${db_name}
MYSQL_USER=${db_username}
MYSQL_PASSWORD=${db_password}


DATABASE_URL=mysql://root:${MYSQL_ROOT_PASSWORD}@database/${MYSQL_DATABASE}?serverVersion=MariaDB-10.11.4

EOL

echo ".env.prod file created successfully!"


