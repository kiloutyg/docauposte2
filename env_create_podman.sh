#!/bin/bash

# Get the github user from the argument
GITHUB_USER=$1
echo "GitHub User: $GITHUB_USER"

# Function to check for uppercase characters
contains_uppercase() {
    [[ "$1" =~ [A-Z] ]]
}
# function to check if the site name is valid and has the first letter uppercase
is_FACILITY_name_valid() {
    [[ "$1" = ^[A-Z] ]]
}

# Prompt for plant trigram
while true; do
    read -p "Please enter your plant trigram (example: lan): " PLANT_TRIGRAM
    if contains_uppercase "$PLANT_TRIGRAM"; then
        echo "The plant trigram should not contain uppercase characters. Please try again."
    else
        break
    fi
    if [ -z "${PLANT_TRIGRAM}" ]
    then
        echo "The plant trigram should not be empty. Please try again."
    fi
done

# Ask the name of the site or plant
while true; do
read -p "Please enter the name of the facility or plant (example: Langres or Andance): " FACILITY_NAME
if is_FACILITY_name_valid "$FACILITY_NAME"; then
    echo "The site name should contain the first letter uppercase. Please try again."
else
        break
    fi
    if [ -z "${FACILITY_NAME}" ]
    then
        echo "The site name should not be empty. Please try again."
    fi
done


# Prompt for database details
read -p "Please enter your MySQL root password: " MYSQL_ROOT_PASSWORD
read -p "Please enter your MySQL username: " MYSQL_USER
read -p "Please enter your MySQL password: " MYSQL_PASSWORD
read -p "Please enter your database name: " MYSQL_DATABASE
while true; do
    read -p "Please enter your app context (prod or dev): " APP_CONTEXT_SH
    if [ "${APP_CONTEXT_SH}" == "prod" ] || [ "${APP_CONTEXT_SH}" == "dev" ]; then
        # If the context is valid, break the loop and continue with the rest of your script
        break
    else
        echo "Invalid app context. Please enter either the word prod or dev."
    fi
done

read -p "What Timezone to use? (default Europe/Paris) " TIMEZONE
if [ -z "${TIMEZONE}" ]
  then
    TIMEZONE="'Europe/Paris'"
fi


while true; do
    read -p "Is there a proxy in your network ? (yes/no) " PROXY_ANSWER;
    if [ "${PROXY_ANSWER}" == "yes" ] || [ "${PROXY_ANSWER}" == "no" ]; then 
      break;
    else
        echo "Please answer yes or no";
    fi
done

if [ "${PROXY_ANSWER}" == "yes" ]
  then
    read -p "Please enter your proxy address(default will be 'http://10.0.0.1'): " PROXY_ADDRESS
      if [ -z "${PROXY_ADDRESS}" ]
        then
        PROXY_ADDRESS="http://10.0.0.1"
      fi
    read -p "Please enter your proxy port(default will be '80'): " PROXY_PORT
      if [ -z "${PROXY_PORT}" ]
        then
        PROXY_PORT="80"
      fi
    PROXY_ENV="${PROXY_ADDRESS}:${PROXY_PORT}"
    PROXY_DOCKERFILE="ENV http_proxy=\'${PROXY_ADDRESS}:${PROXY_PORT}\'"
    sed -i "3s|.*|$PROXY_DOCKERFILE|" docker/dockerfile/Dockerfile
fi

# Create the secrets directory
mkdir -p ./secrets;

# Generate a new secret key
APP_SECRET=$(openssl rand -hex 16)

cat > ./secrets/root_password <<EOL
${MYSQL_ROOT_PASSWORD}
EOL

cat > ./secrets/database_name <<EOL
${MYSQL_DATABASE}
EOL

cat > ./secrets/database_user <<EOL
${MYSQL_USER}
EOL

cat > ./secrets/database_password <<EOL
${MYSQL_PASSWORD}
EOL

# Change the src/Kernel.php to set the good timezone.
cat > ./src/Kernel.php <<EOL
<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function boot(): void
    {
        parent::boot();
        date_default_timezone_set(${TIMEZONE});
    }
}
EOL

# Create .env file
cat > .env <<EOL
MYSQL_ROOT_PASSWORD=${MYSQL_ROOT_PASSWORD}
MYSQL_DATABASE=${MYSQL_DATABASE}
MYSQL_USER=${MYSQL_USER}
MYSQL_PASSWORD=${MYSQL_PASSWORD}
HOSTNAME=${HOSTNAME}
PLANT_TRIGRAM=${PLANT_TRIGRAM}
GITHUB_USER=${GITHUB_USER}
FACILITY_NAME=${FACILITY_NAME}

###> symfony/framework-bundle ###
APP_ENV=${APP_CONTEXT_SH}
APP_SECRET=${APP_SECRET}
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

DATABASE_URL=mysql://root:\${MYSQL_ROOT_PASSWORD}@database/\${MYSQL_DATABASE}?serverVersion=10.11.4-MariaDB

###< doctrine/doctrine-bundle ###

###> symfony/messenger ###
# Choose one of the transports below
# MESSENGER_TRANSPORT_DSN=doctrine://default
# MESSENGER_TRANSPORT_DSN=amqp://guest:guest@localhost:5672/%2f/messages
# MESSENGER_TRANSPORT_DSN=redis://localhost:6379/messages
###< symfony/messenger ###

###> symfony/mailer ###
MAILER_DSN=smtp://smtp.corp.ponet:25?verify_peer=0
MAILER_SENDER_EMAIL=${PLANT_TRIGRAM}.docauposte@opmobility.com
###< symfony/mailer ###
EOL


echo ".env file created successfully!"
if [ "${APP_CONTEXT_SH}" == "prod" ]
    then

        APP_CONTEXT_SH="dev"
        sed -i "s|^APP_ENV=prod.*|APP_ENV=dev|" .env
        sed -i "s|^# MAILER_DSN=.*|MAILER_DSN=smtp://smtp.corp.ponet:25?verify_peer=0|" .env

        set -a
        APP_CONTEXT=${APP_CONTEXT_SH}
        PROXY_ENV=${PROXY_ENV}
        APP_TIMEZONE=${TIMEZONE}
        GITHUB_USER=${GITHUB_USER}
        set +a

        # Create docker-compose.override.yml file to use the good entrypoint
        envsubst < ./template.yml > ./dap.yml;

        podman play kube --replace ./dap.yml

        sleep 180

        podman play kube --down ./dap.yml

        sleep 60

        sed -i "s|^APP_ENV=dev.*|APP_ENV=prod|" .env
        APP_CONTEXT_SH="prod"

        set -a
        APP_CONTEXT=${APP_CONTEXT_SH}
        PROXY_ENV=${PROXY_ENV}
        APP_TIMEZONE=${TIMEZONE}
        GITHUB_USER=${GITHUB_USER}
        set +a

        # Create docker-compose.override.yml file to use the good entrypoint
        envsubst < ./template.yml > ./dap.yml
        echo "Production dap.yml file created successfully!";
        cat ./dap.yml;

    else

        APP_CONTEXT_SH="dev"
        sed -i "s|^APP_ENV=prod.*|APP_ENV=dev|" .env
        sed -i "s|^# MAILER_DSN=.*|MAILER_DSN=smtp://smtp.corp.ponet:25?verify_peer=0|" .env

        set -a
        APP_CONTEXT=${APP_CONTEXT_SH}
        PROXY_ENV=${PROXY_ENV}
        APP_TIMEZONE=${TIMEZONE}
        GITHUB_USER=${GITHUB_USER}
        set +a

        # Create docker-compose.override.yml file to use the good entrypoint
        envsubst < ./template.yml > ./dap.yml;
        echo "Development dap.yml file created successfully!";
        cat ./dap.yml;
fi