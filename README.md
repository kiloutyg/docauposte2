# DocAuPoste2
<<<<<<< HEAD
Created from scratch with Docker, PHP8 and Symfony.
=======

Created from scratch with Docker 20, PHP8.1 and Symfony6.2.6.
>>>>>>> stagingphp

### Git usage basics.

    1 - Create a Github account.
    2 - Add your SSH key to your account folowing this documentation from GitLab : https://docs.gitlab.com/ee/user/ssh.html .
        Quick follow through : 
        a - Create your key pair.
        b - Copy your Pubkey to your account. ( In your profile setting, SSH - GPG keys, new key, then validate)
    3 - Clone the repo locally in the directory of your choosing. 
    4 - Done ! Your ready to go. 
<<<<<<< HEAD
### Run With Docker

#### First version 

Juste clone the git repo and run " docker compose up -d --build " inside the local directory after having personalized your DotEnv. 

=======

### Run With Docker.


#### First version, development version. 

    1 - After cloning the git repo, create a directory namde database_data
    2 - modify your pass in the DotEnv file
    3 - run the " docker compose up --build " command. 
    4 - Inside the web container, in the correct directory (/var/www) type : 
        a - composer install
        b - composer require symfony/webpack-encore-bundle or composer require asset
        c - composer require symfony/apache-pack
        d - composer require --dev symfony/profiler-pack
            - composer require debug
        e - composer require templates
        f - composer require symfony/ux-turbo
        g - yarn add bootstrap --dev && yarn add jquery @popperjs/core --dev
        h - yarn add @fontsource/roboto-condensed --dev  
        i - yarn add @fortawesome/fontawesome-free --dev 
        j - yarn add axios --dev core-js webpack encore webpack-cli webpack-notifier @symfony/webpack-encore
        k - composer require symfony/ux-turbo

    5 - yarn install
    6 - yarn watch ## Mandatory step to execute the webapp correctly ##
    
>>>>>>> stagingphp
