# DocAuPoste2
Created from scratch with Docker, PHP8 and Symfony.

Created from scratch with Docker 20, PHP8.1 and Symfony6.2.6.


## Prerequesite :


##### A - Create your ssh key pair follow the instruction and default option, I STRONGLY ADVISE TO PUT A PASSWORD but it is optional:
```
    ssh-keygen -t ed25519 -C "docauposte2 github"
```

##### B - Copy the key in your clipboard :

From a command prompt on a linux desktop environment :

If xclip can be installed : 
```
    xclip -sel clip < ~/.ssh/id_ed25519.pub
```
Otherwise use cat : 
```
    cat ~/.ssh/id_ed25519.pub
```
From a remote connection to a server from a windows computer for example, print it and then copy it with your mouse or CTRL+C or CTRL+SHIFT+C : 
```
    cat ~/.ssh/id_ed25519.pub 
```

##### C - Paste the key in your github account : 

- Go to your account settings

- In the access area of the summary select SSH and GPG keys

- Click on the button up right "New SSH key"

- Paste the key in the "key" input

- Be nice and give it a name

- Select the type of key it is, most of the time it will be an Authentication Key

- Once everything is done click on "Add SSH key" 



# Semi automated installation :

1 - Download the installation script :

```
    wget https://github.com/polangres/DocAuPoste2/releases/download/v1.1/install-docauposte2.sh 
```
If wget or curl are not available you can use cat as described in the wiki : [https://github.com/polangres/DocAuPoste2/wiki/3-%E2%80%90-Deployment](https://github.com/polangres/DocAuPoste2/wiki/3-%E2%80%90-Deployment)

2 - Render the script executable : 

```   
    sudo chmod +x install-docauposte2.sh
```

3 - Run the script : 

```
    bash install-docauposte2.sh
```



# Manual installation


#### 1 - Install git utility (you can use an other one if you wish to like gh or gitflow):
```
    sudo yum install git
```

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

#### 4 - Once the app launched and IF AN ERROR ABOUT SQL appears when connecting to the app in your web browser : Then prepare Doctrine migration and then migrate : 
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

#### 5 - Run a CHMOD command on the app folder to be sure to stay in control of every file. 

#### 6 - At this point you can begin to configure the App depending on your need. 

#### 7 - IF NEEDED : Modify the value of post_max_size  and upload_max_filesize of the correct php.ini in /usr/local/etc/php respectively line 701 and 853 in the dev one, or line 703 and 855 in the production one.
