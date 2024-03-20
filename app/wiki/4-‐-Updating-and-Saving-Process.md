



#### 1 - Save the work : principle.

Save the Database and the Files. A script will be added to help you with this task in the future. 

It is important to have a different save than the infrastructure one. Murhpy's law will always bite your butt. 

**_Always test those on a non-essential environment or even a test environment before going through the procedure on the production server and environment, it's always quicker to redeploy a test version than to rebuild the whole production server._**

###### a - Web UI

At this point, only the database can be saved through the web UI. 

1. Connect to the Web UI created by the PhpMyAdmin, it should be http://docauposte-address:8082 (e.g. : http://localhost:8082 if hosted on your own machine).

2. Select the database on the right panel.

3. Click on the "Export" tab in the top menu.

4. Select the settings you wish to use, most of the time, the default ones will be good enough. Refer to the official documentation to know more about the options. 

5. Click on the "Export" button on the bottom, it should download the file to your local machine. 


To import a previously exported script : 

1. Make sure that nothing will be lost, in this case do modify the script or find anyway to ensure the continuity of the production operation.

2. Select the destination database.

3. Click on the "Import" tab.

4. Select the appropriate script to upload and execute. 

5. Click on the import button, PMA will display a log of how it went. 


###### b - Command Line the brainy way 

This method will ask you to have some basic knowledge of how docker container work. 
It will also only cover the database side of the things, since saving the documents (uploads and incidents) is just a basic copy and paste action and will be explicated in the last part. 

1. Access the server, through SSH, for example. 

2. Enter and execute bash (or any other shell present in the container, if they have not been modified and are still based on Debian it will be bash) 

Be sure to be in the app directory to execute this next command : 
```
docker compose exec -ti database bash
```
"web" being the name of the container in the compose file.

Otherwise, use the ```docker exec``` command : 
```
docker exec -ti docauposte2-web-1 bash
```
"docauposte2-database-1" being the name of the live container.

3. Once you are in the container, use this command : 

```
mariadb-dump --user yourusername --password databasename > /desired/path/to/db.sql
```
or 
```
mariadb-dump -u yourusername -p databasename > /desired/path/to/db.sql
```

4. Exit the container and copy the file from the container to your host : 

Here is the command to copy to and from a docker container:
```
docker cp <SRC> <DEST>
```
```
docker cp docauposte2-database-1:/path/to/the/dump/file.sql /path/to/desired/destination.sql
```

5. Without entering the container : 

It will also directly export the file from the container to your host. 
```
docker exec docauposte2-database-1 mariadb-dump --user yourusername --password databasename > /desired/path/to/db.dump
```

_**The dumped file should be ready to be imported. **_

To import a previously exported dump script : 

1. Copy the file into the container : 

With ```docker cp``` :
```
docker cp /path/to/desired/destination.sql docauposte2-database-1:/path/to/the/dump/file.sql
```

2. Import the database : 

```
mariadb -u yourusername -p databasename < /desired/path/to/db.sql
```

If, for some reason, the database does not exist, to create it, refer to official documentation to learn more. 


3. Without entering the container : 

```
docker exec -i docauposte2-database-1 mariadb --user=yourusername --password=yourpassword databasename < /desired/path/to/db.sql
```

The database should be restored. Be aware that it still needs for the document to be at the correct path to still work. 


###### C - Command Line, no-brain method : 

This method should work in most cases and does not require that much knowledge beside basic Linux commands or any basics of the environment where the docker system is working. 

You need to have to be a sudoers or for the files to have been opened. 

Copy or the database_data and the doc folder :

```
sudo cp -r /app/path/database_data /desired/destination/path

sudo cp -r /app/path/public/doc desired/destination/path
```


#### 2 - Updating

The updating process is simple. 


###### a - Update the repo from the original repo.

1. First login to your git account.

2. Access the App repository.

3. Sync the fork.


###### b - Update the production environment.

1. Be sure to have done every saving point previously stated. 

2. Stop the containers. 

```
docker stop $(docker ps -qa)
```
or
```
docker compose stop
```

3. If a rebuild is needed, ensure that you will not render the app unusable at the wrong moment. Delete everything to build on a good base.

```
docker system prune -fa 
```

4. Update the code : 

Reset the code to its original version : 
```
git reset --hard
```

Update the code from the git repo : 

```
git pull
```

_Or if, for some reason, you deleted the entirety of the app : _

```
git clone git@github.com:polangres/docauposte2.git
```
or https if the repo is public : 
```
git clone https://github.com/polangres/docauposte2.git
```

5. Place the needed folders and files inside the app directory.

6. Build the app

Enter the app directory : 
```
cd docauposte2/
```

Run the compose command with the build option:
```
docker compose up --build
```

###### c - Test and check.

1. Once the server is on, test the app to see if everything went correctly 

2. Check if the DB is set correctly and it's content still as intended. 


It should be done at this stage. 


Here is a Cat to create the script use to install and update the application : 
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
            git remote remove origin;
            git remote add origin ${GIT_ADDRESS};
            git fetch origin --force;
            git reset --hard origin/main;
            git pull --rebase origin main;
            bash ./env_update.sh;
            sg docker -c "docker compose up --build"
        fi
    fi
fi

OUTER

chmod +x install-docauposte2.sh && echo "install-docauposte2.sh script created successfully!"
```