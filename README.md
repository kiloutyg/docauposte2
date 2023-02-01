# DocAuPoste2
Created from scratch with Docker 20, PHP8.1 and Symfony6.2.6.

### Git usage basics.

    1 - Create a Github account.
    2 - Add your SSH key to your account folowing this documentation from GitLab : https://docs.gitlab.com/ee/user/ssh.html .
        Quick follow through : 
        a - Create your key pair.
        b - Copy your Pubkey to your account. ( In your profile setting, SSH - GPG keys, new key, then validate)
    3 - Clone the repo locally in the directory of your choosing. 
    4 - Done ! Your ready to go. 

### Run With Docker


#### First version 

    1 - After cloning the git repo, create a directory namde database_data
    2 - modify your pass in the DotEnv file
    3 - run the " docker compose up --build " command. 
    