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

    sudo yum install git

2 - Add docker CE repo : 

    sudo subscription-manager repo-override --repo=PlasticOmnium_Docker_Docker_CE_Stable --add=enabled:1

3 - Install Docker :

First uninstall every present Docker component :

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

Then install those component : 

    sudo yum install docker-ce docker-ce-cli containerd.io docker-compose-plugin -y

4 - Docker Post-installation Step.

Create and add the USER to the docker group : 

    sudo groupadd docker
    sudo usermod -aG docker $USER

Log in the new group :

    newgrp docker

Start and Enable auto-start of the necessary process : 

    sudo systemctl start docker
    sudo systemctl start containerd.service
    sudo systemctl enable docker.service
    sudo systemctl enable containerd.service


## Stack installation step : 


1 - Clone the repo:

It will create a folder automatically : 

    git clone https://github.com/kiloutyg/docauposte2
    cd docauposte2


2 - If everything is set as asked in the prerequesite, run :
    
    docker compose up --build
    
    
###  IF AN ERROR ABOUT SQL APPEARS : 

1 - Once the containers are ready and running enter the "web"(yes, it is its name) one : 
    
    docker compose exec -ti web bash
2 - Prepare Doctrine migration and then migrate : 

    php bin/console make:migration
    php bin/console doctrine:migrations:migrate
    
3 - Run a CHMOD command on the app folder to be sure to stay in control of every file. 

4 - At this point you can begin to configure the App depending on your need. 

### IF NEEDED : 
Modify the value of post_max_size  and upload_max_filesize of the correct php.ini in /usr/local/etc/php respectively line 701 and 853 in the dev one, or line 703 and 855 in the production one.





