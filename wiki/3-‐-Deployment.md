# DocAuPoste2
Created from scratch with Docker, PHP8 and Symfony.

Created from scratch with Docker 20, PHP8.1 and Symfony6.2.6.


## Prerequisite :


###### A - Create your ssh key pair follow the instructions and default option, I STRONGLY ADVISE TO PUT A PASSWORD, but it is optional:
```
    ssh-keygen -t ed25519 -C "any_comment_you_wish_to_add"
```

###### B - Copy the key in your clipboard :

From a command prompt on a Linux desktop environment :

If xclip can be installed : 
```
    xclip -sel clip < ~/.ssh/id_ed25519.pub
```
Otherwise, use cat : 
```
    cat ~/.ssh/id_ed25519.pub
```
From a remote connection to a server from a Windows computer, for example, print it and then copy it with your mouse or CTRL+C or CTRL+SHIFT+C : 
```
    cat ~/.ssh/id_ed25519.pub 
```

###### C - Paste the key into your GitHub account : 

- Go to your account settings

- In the access area of the summary, select SSH and GPG keys

- Click on the button up right, "New SSH key"

- Paste the key in the "key" input

- Be nice and give it a name

- Select the type of key it is, most of the time it will be an Authentication Key

- Once everything is done, click on "Add SSH key" 



# Semi automated installation :

###### 1 - Download the installation script :

```
    wget https://github.com/kiloutyg/DocAuPoste2/releases/download/v1.02/install-docauposte2.sh 
```
If wget is not available, you can ```cat``` file directly, as shown at the end of this page(you'll find the whole script with the good command to use)
You can also directly clone the repo as presented later in this wiki or in the readme.md

###### 2 - Render the script executable : 

```   
    sudo chmod +x install-docauposte2.sh
```

###### 3 - Run the script : 

```
    bash install-docauposte2.sh
```



# Manual installation


###### 1 - Install the git utility (you can use another one if you wish to, like gh or gitflow):
```
    sudo yum install git
```

###### 2 - Add docker CE repo : 
```
    sudo subscription-manager repo-override --repo=PlasticOmnium_Docker_Docker_CE_Stable --add=enabled:1
```

###### 3 - Update package manager repository : 
```
    sudo yum update
```

###### 4 - Uninstall any present Docker app :
```
    sudo yum remove docker \
                  docker-client \
                  docker-client-latest \
                  docker-common \
                  docker-latest \
                  docker-latest-logrotate \
                  docker-logrotate \
                  docker-engine \
                  podman \
                  runc
```

###### 5 - Install docker :
```
    sudo yum install docker-ce docker-ce-cli containerd.io docker-compose-plugin -y
```

###### 6 - Docker Post-installation Step.
```
    sudo groupadd docker
    sudo usermod -aG docker $USER
```
```
    newgrp docker
```
```
    sudo systemctl start docker
    sudo systemctl start containerd.service
    sudo systemctl enable docker.service
    sudo systemctl enable containerd.service
```

## Stack installation step : 


###### 1 - Clone the repo:
```
    git clone -b develop git@github.com:kiloutyg/docauposte2
    cd docauposte2
```

###### 2 - Run the DotEnv (to build .env and .env.local, they are similar at this stage) creation script : 
```
    sudo chmod +x env_create.sh
    ./env_create.sh
```

###### 3 - If Docker and Docker compose are installed already, just run (with or without sudo depending on Docker config):
```
    docker compose up --build -d
``` 

###### 4 - Once the app launched and IF AN ERROR ABOUT SQL appears when connecting to the app in your web browser : Then prepare Doctrine migration and then migrate : 
A - Stop the docker compose stack : 
    docker compose stop
or
    CTRL+C

B - Re-run the building command :
``` 
    docker compose up --build -d
```
C - Enter the app container and use the bash command prompt :
```
    docker compose exec -ti web bash
```
D - Run the command to build the database :
```
    php bin/console make:migration
    php bin/console doctrine:migrations:migrate
``` 
E - Exit the container : 
```
    exit
```

###### 5 - Run a CHMOD command on the app folder to be sure to stay in control of every file. 

###### 6 - At this point you can begin to configure the App depending on your need. 

###### 7 - IF NEEDED : Modify the value of post_max_size and upload_max_filesize of the correct php.ini in /usr/local/etc/php respectively line 701 and 853 in the dev one, or line 703 and 855 in the production one.



####### Script to copy and paste in command prompt : 

```
cat > install-docauposte2.sh <<'OUTER'
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

            cd docauposte2;
            sg docker -c "docker compose stop";
            git remote set-url --add origin ${GIT_ADDRESS};
            git remote set-url --delete origin git@github.com:polangres/docauposte2;
            git fetch origin --force;
            git reset HARD --force;
            git pull --rebase origin main;
            bash ./env_update.sh;
            sg docker -c "docker compose up --build"
        fi
    fi
fi
OUTER

chmod +x install-docauposte2.sh && echo "install-docauposte2.sh script created successfully!"
```