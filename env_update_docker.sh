#!/bin/bash

# Get the github user from the argument
GITHUB_USER=$1;

echo "GitHub User: $GITHUB_USER";

# function to check if the site name is valid and has the first letter uppercase
is_FACILITY_name_valid() {
    [[ "$1" =~ ^[A-Z] ]]
}


# add_to_file() {
#     local file="$1"
#     local entry="$2"
#     if ! grep -q "^${entry}$" "$file"; then
#         echo "$entry" | tee -a "$file" > /dev/null
#         echo "Added $entry to $file"
#     else
#         echo "$entry already exists in $file"
#     fi
# }


# Ask the name of the site or plant
while true; do
    read -p "Please enter the name of the facility or plant (example: Langres or Andance): " FACILITY_NAME
    if [ -z "${FACILITY_NAME}" ]; then
        echo "The site name should not be empty. Please try again."
    elif ! is_FACILITY_name_valid "$FACILITY_NAME"; then
        echo "The site name should start with an uppercase letter. Please try again."
    else
        break
    fi
done

# Function to check for uppercase characters
contains_uppercase() {
    [[ "$1" =~ [A-Z] ]]
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

read -p "What Timezone to use? (default Europe/Paris) " TIMEZONE
if [ -z "${TIMEZONE}" ]
  then
    TIMEZONE="Europe/Paris"
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
    HTTP_PROXY_ENV="      http_proxy: ${PROXY_ADDRESS}:${PROXY_PORT}"
    HTTPS_PROXY_ENV="      https_proxy: ${PROXY_ADDRESS}:${PROXY_PORT}"
    PROXY_DOCKERFILE="ENV http_proxy=\'${PROXY_ADDRESS}:${PROXY_PORT}\'"
    sed -i "3s|.*|$PROXY_DOCKERFILE|" docker/dockerfile/Dockerfile
fi


APP_CONTEXT="dev"

sed -i "s|^APP_ENV=prod.*|APP_ENV=dev|" .env;
sed -i "s|^# MAILER_DSN=.*|MAILER_DSN=smtp://smtp.corp.ponet:25?verify_peer=0|" .env;
sed -i "s|^# MAILER_SENDER_EMAIL=.* |MAILER_SENDER_EMAIL=${PLANT_TRIGRAM}.docauposte@opmobility.com|" .env;


# Define the SSL directory
SSL_DIR="./secrets/ssl"

# Check if SSL directory exists
if [ -d "$SSL_DIR" ]; then
    echo "SSL directory exists: $SSL_DIR"
else
    echo "SSL directory does not exist: $SSL_DIR"
    echo "Executing script to create SSL directory and certificates..."

    # Execute the script to create the directory and certificates
    ./cert-gen.sh

    # Check if the SSL directory now exists
    if [ -d "$SSL_DIR" ]; then
        echo "SSL directory and certificates created successfully."
    else
        echo "Error: Failed to create SSL directory and certificates."
        exit 1
    fi
fi


variables=(
    "HOSTNAME=${HOSTNAME}"
    "PLANT_TRIGRAM=${PLANT_TRIGRAM}"
    "GITHUB_USER=${GITHUB_USER}"
    "FACILITY_NAME=${FACILITY_NAME}"
    "MYSQL_SSL_KEY=/etc/ssl/certs/server-key.pem"
    "MYSQL_SSL_CERT=/etc/ssl/certs/server-cert.pem"
    "MYSQL_SSL_CA=/etc/ssl/certs/ca-cert.pem"
)

for var in "${variables[@]}"; do
    key="${var%%=*}"
    value="${var#*=}"

    # Escape special characters for sed
    escaped_key=$(printf '%s\n' "$key" | sed 's/[]\/$*.^|[]/\\&/g')
    escaped_value=$(printf '%s\n' "$value" | sed 's/[\/&$*.^|]/\\&/g')

    if grep -q "^${escaped_key}=" .env; then
        sed -i "s|^${escaped_key}=.*|${escaped_key}=${escaped_value}|" .env
        echo "Updated ${key} in .env"
    else
        if grep -q "^MYSQL_PASSWORD=" .env; then
            sed -i "/^MYSQL_PASSWORD=/a\\
${escaped_key}=${escaped_value}" .env
            echo "Added ${key} after MYSQL_PASSWORD= in .env"
        else
            echo "${escaped_key}=${escaped_value}" >> .env
            echo "Added ${key} at the end of .env"
        fi
    fi
done


cat > src/Kernel.php <<EOL
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
        date_default_timezone_set('${TIMEZONE}');
    }
}
EOL


# Create docker-compose.override.yml file to use the good entrypoint
cat > docker-compose.override.yml <<EOL
services:
  web:
    image: ghcr.io/${GITHUB_USER}/docauposte2:main
    restart: unless-stopped 
    entrypoint: "./${APP_CONTEXT}-entrypoint.sh"
    environment:
${HTTP_PROXY_ENV}
${HTTPS_PROXY_ENV}
      APP_TIMEZONE: ${TIMEZONE}
    volumes:
      - ./:/var/www
    labels:
      - traefik.enable=true
      - traefik.http.routers.webdap.rule=PathPrefix(\`/docauposte\`)
      - traefik.http.routers.webdap.middlewares=strip-webdap-prefix
      - traefik.http.middlewares.strip-webdap-prefix.stripprefix.prefixes=/docauposte
      - traefik.http.routers.webdap.entrypoints=web
    depends_on:
      - database
    networks:
      vpcbr:
        ipv4_address: 172.21.0.4
EOL


sg docker -c "docker compose up --build -d";

# Wait until the container is listed as running
until [ "$(docker ps -q -f name=docauposte2-web-1)" ]; do
  echo "Waiting for container docauposte2-web-1 to start..."
  sleep 1
done

# Wait until the webpack compiled successfully
until docker compose logs --since 10s --tail 10 web 2>&1 | grep -q "webpack compiled successfully"; do
  echo "Waiting for the webpack to be compiled" 
  sleep 10
done

echo "Webpack compiled successfully";

sg docker -c "docker compose stop";

sleep 30;

sed -i "s|APP_ENV=dev.*|APP_ENV=prod|" .env;

APP_CONTEXT="prod";

# Create docker-compose.override.yml file to use the good entrypoint
cat > docker-compose.override.yml <<EOL
services:
  web:
    image: ghcr.io/${GITHUB_USER}/docauposte2:main
    restart: unless-stopped 
    entrypoint: "./${APP_CONTEXT}-entrypoint.sh"
    environment:
${HTTP_PROXY_ENV}
${HTTPS_PROXY_ENV}
      APP_TIMEZONE: ${TIMEZONE}
    volumes:
      - ./:/var/www
    labels:
      - traefik.enable=true
      - traefik.http.routers.webdap.rule=PathPrefix(\`/docauposte\`)
      - traefik.http.routers.webdap.middlewares=strip-webdap-prefix
      - traefik.http.middlewares.strip-webdap-prefix.stripprefix.prefixes=/docauposte
      - traefik.http.routers.webdap.entrypoints=web
    depends_on:
      - database
    networks:
      vpcbr:
        ipv4_address: 172.21.0.4
EOL
