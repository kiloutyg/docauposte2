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
    sudo yum install -y git yum-utils;
    sudo yum-config-manager --add-repo https://download.docker.com/linux/rhel/docker-ce.repo;

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

    sudo yum install docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin -y;

# Add the user to the docker group
    sudo groupadd docker;
    sudo usermod -aG docker $USER;

# Start docker and enable it inside a prompt with the docker group
sg docker -c "
    sudo systemctl start docker;
    sudo systemctl start containerd.service;
    sudo systemctl enable docker.service;
    sudo systemctl enable containerd.service;"


# Function to check for uppercase characters
contains_uppercase() {
    [[ "$1" =~ [A-Z] ]]
}

# Ask the user for name of its github user 
while true; do
    read -p "Name of your github user (example: polangres) :  " GITHUB_USER
    if contains_uppercase "$GITHUB_USER"; then
        echo "The github user name should not contain uppercase characters. Please try again."
    else
        break
    fi
done

# Ask the user for the git repository address either in ssh or http
    read -p "Address of the git repository (ssh or http // default: https://github.com/${GITHUB_USER}/docauposte2 ) :  " GIT_ADDRESS;
    if [ -z "${GIT_ADDRESS}" ]
    then
        GIT_ADDRESS="https://github.com/${GITHUB_USER}/docauposte2"
    fi

# Clone the git repository and run the env_create.sh script
    git clone ${GIT_ADDRESS};
    
    cd docauposte2;

    bash ./env_create.sh ${GITHUB_USER};

# Build the docker containers
    sg docker -c "docker compose up --build &"
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
        sg docker -c "docker compose up -d"
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

            # Function to check for uppercase characters
            contains_uppercase() {
                [[ "$1" =~ [A-Z] ]]
            }

            # Ask the user for name of its github user 
            while true; do
                read -p "Name of your github user (example: polangres) :  " GITHUB_USER
                if contains_uppercase "$GITHUB_USER"; then
                    echo "The github user name should not contain uppercase characters. Please try again."
                else
                    break
                fi
            done


        # Ask the user for the git repository address either in ssh or http
            read -p "Address of the git repository (ssh or http // default: https://github.com/${GITHUB_USER}/docauposte2 ) :  " GIT_ADDRESS;
            if [ -z "${GIT_ADDRESS}" ]
            then
                GIT_ADDRESS="https://github.com/${GITHUB_USER}/docauposte2"
            fi
            cd docauposte2;
            sg docker -c "docker compose stop";
            sg docker -c "docker system prune -fa";
            git remote remove origin;
            # Remove every before https in the GIT_ADDRESS
            GIT_ADDRESS=$(echo ${GIT_ADDRESS} | sed 's|.*\(https\)|\1|')
            git remote add origin ${GIT_ADDRESS};
            git fetch origin --force;
            git reset --hard origin/main;
            git pull --rebase origin main;
            bash ./env_update.sh ${GITHUB_USER};
            sg docker -c "docker compose up --build -d"
        fi
    fi
fi
