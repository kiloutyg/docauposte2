#!/bin/sh

# Install prerequisite packages

sudo subscription-manager repo-override --repo=PlasticOmnium_Docker_Docker_CE_Stable --add=enabled:1;

sudo yum remove docker \
              docker-client \
              docker-client-latest \
              docker-common \
              docker-latest \
              docker-latest-logrotate \
              docker-logrotate \
              docker-engine \
              podman \
              runc;

sudo yum install docker-ce docker-ce-cli containerd.io docker-compose-plugin -y;

sudo groupadd docker;
sudo usermod -aG docker $USER;

newgrp docker;

sudo systemctl start docker;
sudo systemctl start containerd.service;
sudo systemctl enable docker.service;
sudo systemctl enable containerd.service;