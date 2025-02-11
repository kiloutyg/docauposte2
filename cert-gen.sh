#!/bin/bash

# Create the ssl certificate directory
mkdir -p ./secrets/ssl/


# Generate CA certificate
openssl genrsa 2048 > ./secrets/ssl/ca-key.pem

openssl req -new -x509 -nodes -days 3600 \
    -key ./secrets/ssl/ca-key.pem -out ./secrets/ssl/ca-cert.pem \
    -subj "/CN=MariaDB CA"



# Generate server certificate with an appropriate subject and unique serial number
openssl req -newkey rsa:2048 -days 3600 \
    -nodes -keyout ./secrets/ssl/server-key.pem \
    -out ./secrets/ssl/server-req.pem \
    -subj "/CN=database"

openssl x509 -req -in ./secrets/ssl/server-req.pem -days 3600 \
    -CA ./secrets/ssl/ca-cert.pem -CAkey ./secrets/ssl/ca-key.pem -set_serial 01 \
    -out ./secrets/ssl/server-cert.pem



# Generate client certificate with a different serial number
openssl req -newkey rsa:2048 -days 3600 \
    -nodes -keyout ./secrets/ssl/client-key.pem \
    -out ./secrets/ssl/client-req.pem \
    -subj "/CN=MariaDB Client"

openssl x509 -req -in ./secrets/ssl/client-req.pem -days 3600 \
    -CA ./secrets/ssl/ca-cert.pem -CAkey ./secrets/ssl/ca-key.pem -set_serial 02 \
    -out ./secrets/ssl/client-cert.pem