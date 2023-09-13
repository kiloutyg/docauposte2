#!/bin/bash

# Prompt for database details
read -p "Please enter your MySQL root password: " MYSQL_ROOT_PASSWORD
read -p "Please enter your MySQL username: " MYSQL_USER
read -p "Please enter your MySQL password: " MYSQL_PASSWORD
read -p "Please enter your database name: " MYSQL_DATABASE
while true; do
    read -p "Please enter your app context (prod or dev): " APP_CONTEXT
    if [ "$APP_CONTEXT" == "prod" ] || [ "$APP_CONTEXT" == "dev" ]; then
        # If the context is valid, break the loop and continue with the rest of your script
        break
    else
        echo "Invalid app context. Please enter either prod or dev."
    fi
done
# APP_SECRET=96ae0f3daef954cfbcb61ad63652ca85
APP_SECRET=$(openssl rand -hex 16)

# Create docker-compose.override.yml file to use the good entrypoint
if [ "$APP_CONTEXT" == "prod" ]; then
cat > docker-compose.override.yml <<EOL
version: '3.8'

services:
  web:
    entrypoint: "./entrypoint.sh"
EOL
else
cat > docker-compose.override.yml <<EOL
version: '3.8'

services:
  web:
    entrypoint: "./dev-entrypoint.sh"
EOL
fi


# Create .env file
cat > .env <<EOL
MYSQL_ROOT_PASSWORD=${MYSQL_ROOT_PASSWORD}
MYSQL_DATABASE=${MYSQL_DATABASE}
MYSQL_USER=${MYSQL_USER}
MYSQL_PASSWORD=${MYSQL_PASSWORD}

###> symfony/framework-bundle ###
APP_ENV=${APP_CONTEXT}
APP_SECRET=${APP_SECRET}
APP_TIMEZONE=Europe/Paris
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

DATABASE_URL=mysql://root:\${MYSQL_ROOT_PASSWORD}@database/\${MYSQL_DATABASE}?serverVersion=MariaDB-10.11.4

###< doctrine/doctrine-bundle ###

###> symfony/messenger ###
# Choose one of the transports below
# MESSENGER_TRANSPORT_DSN=doctrine://default
# MESSENGER_TRANSPORT_DSN=amqp://guest:guest@localhost:5672/%2f/messages
# MESSENGER_TRANSPORT_DSN=redis://localhost:6379/messages
###< symfony/messenger ###

###> symfony/mailer ###
MAILER_DSN=smtp://smtp.corp.ponet:25?verify_peer=0
###< symfony/mailer ###
EOL


echo ".env file created successfully!"
