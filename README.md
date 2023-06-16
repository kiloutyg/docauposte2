# DocAuPoste2
Created from scratch with Docker, PHP8 and Symfony.

Created from scratch with Docker 20, PHP8.1 and Symfony6.2.6.

### Git usage basics.

    1 - Create a Github account.
    2 - Add your SSH key to your account folowing this documentation from GitLab : https://docs.gitlab.com/ee/user/ssh.html .
        Quick follow through : 
        a - Create your key pair.
        b - Copy your Pubkey to your account. ( In your profile setting, SSH - GPG keys, new key, then validate)
    3 - Clone the repo locally in the directory of your choosing. 
    4 - Done ! Your ready to go. 

# Latest Version : 
## Prerequesite :


1 - Install git :

A - Install git foss utility :
```
    sudo yum install git
```
B - Create your ssh key pair follow the instruction and default option, I STRONGLY ADVISE TO PUT A PASSWORD but it is optional:
```
    ssh-keygen -t ed25519 -C "any_comment_you_wish_to_add"
```
C - Copy the key in your clipboard :

a - From a command prompt on a linux desktop environment :
```
    xclip -sel clip < ~/.ssh/id_ed25519.pub
```
b - From a remote connection to a server from a windows computer for example, print it and then copy it with your mouse or CTRL+C or CTRL+SHIFT+C : 
```
    cat ~/.ssh/id_ed25519.pub 
```
D - Paste the key in your github account : 

- Go to your account settings

- In the access area of the summary select SSH and GPG keys

- Click on the button up right "New SSH key"

- Paste the key in the "key" input

- Be nice and give it a name

- Select the type of key it is, most of the time it will be an Authentication Key

- Once everything is done click on "Add SSH key" 


2 - Add docker CE repo : 
```
    sudo subscription-manager repo-override --repo=PlasticOmnium_Docker_Docker_CE_Stable --add=enabled:1
```
3 - Install Docker :
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
```
    sudo yum install docker-ce docker-ce-cli containerd.io docker-compose-plugin -y
```
4 - Docker Post-installation Step.
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


1 - Clone the repo:
```
    git clone https://github.com/kiloutyg/docauposte2
    cd docauposte2
```
2 - Run the DotEnv creation script : 
```
    sudo chmod +x env_create.sh
    ./env_create.sh
```
3 - If Docker and Docker compose are installed already, just run (with or without sudo depending of Docker config):
```
    docker compose up --build
``` 
4 - Once the containers are ready and running enter the "web"(yes, it is its name) one : 
```
    docker compose exec -ti web bash
```
5 - IF AN ERROR ABOUT SQL POP when connecting to the app in your web browser : Then prepare Doctrine migration and then migrate : 
```
    php bin/console make:migration
    php bin/console doctrine:migrations:migrate
``` 
6 - Run a CHMOD command on the app folder to be sure to stay in control of every file. 

7 - At this point you can begin to configure the App depending on your need. 

8 - IF NEEDED : Modify the value of post_max_size  and upload_max_filesize of the correct php.ini in /usr/local/etc/php respectively line 701 and 853 in the dev one, or line 703 and 855 in the production one.





