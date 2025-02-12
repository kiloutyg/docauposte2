#!/bin/bash

# Set ownership to mysql user
chown mysql:mysql /dap-db-certs/*.pem

# Set permissions for certificate files
chmod 644 /dap-db-certs/ca-cert.pem /dap-db-certs/server-cert.pem

# Set secure permissions for private key
chmod 644 /dap-db-certs/server-key.pem