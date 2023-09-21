#!/bin/bash
# Ask the user if they have already run the app
read -p "Are you running the app for the first Time ?(yes/no) " ANSWER;

while true; do
# Check if the user answered yes or no
    if [ "${ANSWER}" = "yes" ] || [ "${ANSWER}" = "no" ]; then
        break
    else
        echo "Please answer yes or no"; 
    fi
done

# If the user answered yes, we install the app
if [ "${ANSWER}" == "yes" ]
then 

# Install git and PlasticOmnium docker repo
    sudo yum install -y git;
    sudo subscription-manager repo-override --repo=PlasticOmnium_Docker_Docker_CE_Stable --add=enabled:1;

# Remove old docker version and install docker-ce
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

# Add the user to the docker group
    sudo groupadd docker;
    sudo usermod -aG docker $USER;

# Start docker and enable it inside a prompt with the docker group
sg docker -c "
    sudo systemctl start docker;
    sudo systemctl start containerd.service;
    sudo systemctl enable docker.service;
    sudo systemctl enable containerd.service;"

# Ask the user for the git repository address either in ssh or http
    read -p "Address of the git repository (ssh or http ) :  " GIT_ADDRESS;

# Clone the git repository and run the env_create.sh script
    git clone ${GIT_ADDRESS};
    cd docauposte2;

    bash ./env_create.sh;

# Build the docker containers
    sg docker -c "docker compose up --build"
else
    cd docauposte2;
    sg docker -c "docker compose up"
fi
