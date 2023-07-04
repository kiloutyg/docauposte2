#!/bin/bash
read -p "Are you running the app for the first Time ?(yes/no) " ANSWER;

if [ "${ANSWER}" != "yes" ] && [ "${ANSWER}" != "no" ]
then 
    echo "Please answer yes or no";
    read -p "Are you running the app for the first Time ?(yes/no) " ANSWER;
fi

if [ "${ANSWER}" == "yes" ]
then 
    sudo yum install -y git;
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

    read -p "Are you running the app for the first Time ?(yes/no) " GIT_ADDRESS;
    git clone ${GIT_ADDRESS};
    cd docauposte2;
    bash ./prerequisites.sh;
    bash ./env_create.sh;
    docker compose up --build;
else 
    docker compose up;
fi
