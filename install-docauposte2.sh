#!/bin/bash
# Ask the user if they have already run the app
while true; do
read -p "Are you running the app for the first Time ? (yes/no) " ANSWER;
# Check if the user answered yes or no
    if [ "${ANSWER}" == "yes" ] || [ "${ANSWER}" == "no" ]; then 
        break
        else
            echo "Please answer by yes or no";
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
    read -p "Address of the git repository (ssh or http // default: https://github.com/polangres/docauposte2 ) :  " GIT_ADDRESS;
    if [ -z "${GIT_ADDRESS}" ]
    then
        GIT_ADDRESS="https://github.com/polangres/docauposte2"
    fi
echo -e "Host github.com\n\tStrictHostKeyChecking no\n" >> ~/.ssh/config

# Clone the git repository and run the env_create.sh script
    git clone ${GIT_ADDRESS};
    
    cd docauposte2;

    bash ./env_create.sh;

# Build the docker containers
    sg docker -c "docker compose up --build"
else
# If the user answered no, we will ask if he wants to launch the app or if he wants to update it
while true; do
    read -p "Do you wish to launch the app ? (yes/no) " LAUNCH_ANSWER;
    if [ "${LAUNCH_ANSWER}" == "yes" ] || [ "${LAUNCH_ANSWER}" == "no" ]; then
        break
        else
            echo "Please answer by yes or no";
    fi
done
# If the user answered yes, we launch the app
    if [ "${LAUNCH_ANSWER}" == "yes" ]; then
        cd docauposte2;
        sg docker -c "docker compose up"
        else
            while true; do
                read -p "Do you wish to update the app ? (yes/no) " UPDATE_ANSWER;
                if [ "${UPDATE_ANSWER}" == "yes" ] || [ "${UPDATE_ANSWER}" == "no" ]; then
                    break
                    else
                        echo "Please answer by yes or no";
                fi
            done
        if [ "${UPDATE_ANSWER}" == "yes" ]; then
        # Ask the user for the git repository address either in ssh or http
            read -p "Address of the git repository (ssh or http // default: https://github.com/polangres/docauposte2 ) :  " GIT_ADDRESS;
            if [ -z "${GIT_ADDRESS}" ]
            then
                GIT_ADDRESS="https://github.com/polangres/docauposte2"
            fi
            cd docauposte2;
            sg docker -c "docker compose stop";
            git remote set-url --add origin ${GIT_ADDRESS};
            git remote set-url --delete origin git@github.com:polangres/docauposte2;
            git fetch origin --force;
            git reset HARD;
            git pull --rebase origin main;
            bash ./env_update.sh;
            sg docker -c "docker compose up --build"
        fi
    fi
fi
