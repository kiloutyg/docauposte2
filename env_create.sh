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


# MySQL settings
MYSQL_ROOT_PASSWORD=$(openssl rand -hex 8)
MYSQL_DATABASE=${db_name}
MYSQL_USER=${db_username}
MYSQL_PASSWORD=${db_password}

###> symfony/framework-bundle ###
APP_ENV=prod
APP_SECRET=${app_secret}
SYMFONY_DEPRECATIONS_HELPER=weak
###< symfony/framework-bundle ###

###> symfony/webapp-pack ###
MESSENGER_TRANSPORT_DSN=doctrine://default?auto_setup=0
###< symfony/webapp-pack ###

###> doctrine/doctrine-bundle ###
# Format described at https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html#connecting-using-a-url
# IMPORTANT: You MUST configure your server version, either here or in config/packages/doctrine.yaml
#
# DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"
# DATABASE_URL="mysql://app:!ChangeMe!@127.0.0.1:3306/app?serverVersion=8&charset=utf8mb4"
DATABASE_URL=mysql://root:${MYSQL_ROOT_PASSWORD}@database/${MYSQL_DATABASE}?serverVersion=MariaDB-10.11.4
###< doctrine/doctrine-bundle ###

###> symfony/messenger ###
# Choose one of the transports below
# MESSENGER_TRANSPORT_DSN=doctrine://default
# MESSENGER_TRANSPORT_DSN=amqp://guest:guest@localhost:5672/%2f/messages
# MESSENGER_TRANSPORT_DSN=redis://localhost:6379/messages
###< symfony/messenger ###

###> symfony/mailer ###
# MAILER_DSN=null://null
###< symfony/mailer ###
EOL

echo ".env.prod file created successfully!"


