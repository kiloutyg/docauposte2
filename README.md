# DocAuPoste2
Created from scratch with Docker, PHP8 and Symfony.

Created from scratch with Docker 20, PHP8.1 and Symfony6.2.6.


# Semi automated installation :

1 - Download the installation script :

```
    wget https://github.com/kiloutyg/DocAuPoste2/releases/download/v0.92.6/install-docauposte2.sh 
```

2 - Render the script executable : 

```   
    sudo chmod +x install-docauposte2.sh
```

3 - Run the script : 

```
    bash install-docauposte2.git
```

# Manual installation

## Prerequesite :


#### 1 - Install git :

##### A - Install git utility (you can use an other one if you wish to like gh or gitflow):
```
    sudo yum install git
```
##### B - Create your ssh key pair follow the instruction and default option, I STRONGLY ADVISE TO PUT A PASSWORD but it is optional:
```
    ssh-keygen -t ed25519 -C "any_comment_you_wish_to_add"
```
##### C - Copy the key in your clipboard :

a - From a command prompt on a linux desktop environment :
```
    xclip -sel clip < ~/.ssh/id_ed25519.pub
```
b - From a remote connection to a server from a windows computer for example, print it and then copy it with your mouse or CTRL+C or CTRL+SHIFT+C : 
```
    cat ~/.ssh/id_ed25519.pub 
```
##### D - Paste the key in your github account : 

- Go to your account settings

- In the access area of the summary select SSH and GPG keys

- Click on the button up right "New SSH key"

- Paste the key in the "key" input

- Be nice and give it a name

- Select the type of key it is, most of the time it will be an Authentication Key

- Once everything is done click on "Add SSH key" 


#### 2 - Add docker CE repo : 
```
    sudo subscription-manager repo-override --repo=PlasticOmnium_Docker_Docker_CE_Stable --add=enabled:1
```
#### 3 - Update package manager repository : 
```
    sudo yum update
```
#### 4 - Uninstall any present Docker app :
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
#### 5 - Install docker :
```
    sudo yum install docker-ce docker-ce-cli containerd.io docker-compose-plugin -y
```
#### 6 - Docker Post-installation Step.
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


#### 1 - Clone the repo:
```
    git clone -b develop git@github.com:kiloutyg/docauposte2
    cd docauposte2
```
#### 2 - Run the DotEnv (to build .env and .env.local, they are similar at this stage) creation script : 
```
    sudo chmod +x env_create.sh
    ./env_create.sh
```
#### 3 - If Docker and Docker compose are installed already, just run (with or without sudo depending of Docker config):
```
    docker compose up --build -d
``` 
#### 4 - Once the app launched and IF AN ERROR ABOUT SQL POP when connecting to the app in your web browser : Then prepare Doctrine migration and then migrate : 
A - Stop the docker compose stack : 
    docker compose stop
or
    CTRL+C

B - Append the .env and .env.local from APP_ENV=prod to APP_ENV=dev :
```
    sed -i 's/prod/dev/' .env
    sed -i 's/prod/dev/' .env.local

    sed -i 's/--no-dev/--dev/' entrypoint.sh
    sed -i 's/add/add -D/' entrypoint.sh

    sed -i 's/yarn encore production/yarn encore dev --watch/' entrypoint.sh
```
C - Re-run the building command :
``` 
    docker compose up --build -d
```
D - Enter the app container and use the bash command prompt :
```
    docker compose exec -ti web bash
```
E - Run the command to build the database :
```
    php bin/console make:migration
    php bin/console doctrine:migrations:migrate
``` 
F - Exit the container : 
```
    exit
```
G - Once you confirm that the app work as intended and display the superadmin creation interface repeat the action from A to C but with using these command instead of the ones present in B :
```
    sed -i 's/dev/prod/' .env
    sed -i 's/dev/prod/' .env.local

    sed -i 's/--dev/--no-dev/' entrypoint.sh
    sed -i 's/add -D/add/' entrypoint.sh

    sed -i 's/yarn encore dev --watch/yarn encore production/' entrypoint.sh
```


#### 5 - Run a CHMOD command on the app folder to be sure to stay in control of every file. 

#### 6 - At this point you can begin to configure the App depending on your need. 

#### 7 - IF NEEDED : Modify the value of post_max_size  and upload_max_filesize of the correct php.ini in /usr/local/etc/php respectively line 701 and 853 in the dev one, or line 703 and 855 in the production one.





