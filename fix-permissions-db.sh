#!/bin/bash

# Set ownership to mysql user
chown mysql:mysql /db-certs/*.pem

# Set permissions for certificate files
chmod 644 /db-certs/ca-cert.pem /db-certs/server-cert.pem

# Set secure permissions for private key
chmod 644 /db-certs/server-key.pem