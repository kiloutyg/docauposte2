#!/bin/bash
read -p "Are you running the app for the first Time ?(yes/no) " ANSWER

if [ "${ANSWER}" != "yes" ] && [ "${ANSWER}" != "no" ]
then 
    echo "Please answer yes or no"
    read -p "Are you running the app for the first Time ?(yes/no) " ANSWER
fi

if [ "${ANSWER}" == "yes" ]
then 
    bash ./env_create.sh
    docker compose up --build
else 
    docker compose up
fi
