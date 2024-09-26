#!/bin/bash

# Get the github user and the podman from the argument
GITHUB_USER=$1
PODMAN=$2

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

sudo yum install -y git yum-utils;

# If the user answered yes, we install the app
if [ "${ANSWER}" == "yes" ]; then 

    # Ask the user for the git repository address either in ssh or http
    read -p "Address of the git repository (ssh or http // default: https://github.com/${GITHUB_USER}/docauposte2 ) :  " GIT_ADDRESS;
    if [ -z "${GIT_ADDRESS}" ]
    then
        GIT_ADDRESS="https://github.com/${GITHUB_USER}/docauposte2"
    fi

    # Clone the git repository and run the env_create.sh script
    git clone ${GIT_ADDRESS};

    cd docauposte2;

    if [ "${PODMAN}" == "no" ]; then

        # Function to check for uppercase characters
        contains_uppercase() {
            [[ "$1" =~ [A-Z] ]]
        }

        # Ask the user for its github token
        # while true; do
        #     read -p "Github Personal Access Token ( ):  " GITHUB_TOKEN;
        #     if [ -z "${GITHUB_TOKEN}" ]
        #     then
        #         echo "The github token should not be empty. Please try again."
        #     else
        #         break
        #     fi
        # done

        # Install git and PlasticOmnium docker repo
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

        # Connect to the github docker registry
        # docker login ghcr.io -u $GITHUB_USER -p $GITHUB_TOKEN;

        # Start docker and enable it inside a prompt with the docker group
        sg docker -c "
            sudo systemctl start docker;
            sudo systemctl start containerd.service;
            sudo systemctl enable docker.service;
            sudo systemctl enable containerd.service;"

        bash ./env_create_docker.sh ${GITHUB_USER};

        # Build the docker containers
        sg docker -c "docker compose up --build -d"

    else

        bash ./env_create_podman.sh ${GITHUB_USER};

        podman play kube ./dap-pma-mariadb.yml;

    fi

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
        if [ "${PODMAN}" == "no" ]; then
            sg docker -c "docker compose up -d"
        else
            podman play kube --replace ./dap-pma-mariadb.yml;
        fi
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

        # Ask the user for the git repository address either in ssh or http
            read -p "Address of the git repository (ssh or http // default: https://github.com/${GITHUB_USER}/docauposte2 ) :  " GIT_ADDRESS;
            if [ -z "${GIT_ADDRESS}" ]
            then
                GIT_ADDRESS="https://github.com/${GITHUB_USER}/docauposte2"
            fi
            cd docauposte2;

            if [ "${PODMAN}" == "no" ]; then
            sg docker -c "docker compose stop";
                sg docker -c "docker system prune -fa";
            else
                podman play kube --down ./dap-pma-mariadb.yml;
                podman system prune -fa;
            fi

            git remote remove origin;
            # Remove everything before https in the GIT_ADDRESS
            GIT_ADDRESS=$(echo ${GIT_ADDRESS} | sed 's|.*\(https\)|\1|')
            git remote add origin ${GIT_ADDRESS};
            git fetch origin --force;
            git reset --hard origin/main;
            git pull --rebase origin main;

            if [ "${PODMAN}" == "no" ]; then
                bash ./env_update_docker.sh ${GITHUB_USER};
                sg docker -c "docker compose up --build -d"
            else
                bash ./env_update_podman.sh ${GITHUB_USER};
                podman play kube --replace ./dap-pma-mariadb.yml;
            fi
        fi
    fi
fi
