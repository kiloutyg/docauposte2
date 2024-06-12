#!/bin/bash

cat > ~/.ssh/config <<EOL
Host github.com
    StrictHostKeyChecking no
EOL

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
    PROXY_ENV="      http_proxy: ${PROXY_ADDRESS}:${PROXY_PORT}"
    PROXY_DOCKERFILE="ENV http_proxy=\'${PROXY_ADDRESS}:${PROXY_PORT}\'"
    sed -i "3s|.*|$PROXY_DOCKERFILE|" docker/dockerfile/Dockerfile
fi

APP_CONTEXT="dev"
sed -i "s|^APP_ENV=prod.*|APP_ENV=dev|" .env
sed -i "s|^# MAILER_DSN=.*|MAILER_DSN=smtp://smtp.corp.ponet:25?verify_peer=0|" .env
sed -i "s|^# MAILER_SENDER_EMAIL=.* |MAILER_SENDER_EMAIL=lan.docauposte@opmobility.com|" .env
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
        date_default_timezone_set(${TIMEZONE});
    }
}
EOL


# Create docker-compose.override.yml file to use the good entrypoint
cat > docker-compose.override.yml <<EOL
version: '3.8'

services:
  web:
    image: ghcr.io/polangres/docauposte2:main
    restart: unless-stopped 
    entrypoint: "./${APP_CONTEXT}-entrypoint.sh"
    environment:
${PROXY_ENV}
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
networks:
  vpcbr:
    driver: bridge
    ipam:
      config:
        - subnet: 172.21.0.0/16
          gateway: 172.21.0.1

EOL


sg docker -c "docker compose up --build -d"

sleep 90

sg docker -c "docker compose stop"

sleep 30

sed -i "s|^APP_ENV=dev.*|APP_ENV=prod|" .env
APP_CONTEXT="prod"


# Create docker-compose.override.yml file to use the good entrypoint
cat > docker-compose.override.yml <<EOL
version: '3.8'

services:
  web:
    image: ghcr.io/polangres/docauposte2:main
    restart: unless-stopped 
    entrypoint: "./${APP_CONTEXT}-entrypoint.sh"
    environment:
${PROXY_ENV}
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
networks:
  vpcbr:
    driver: bridge
    ipam:
      config:
        - subnet: 172.21.0.0/16
          gateway: 172.21.0.1

EOL
