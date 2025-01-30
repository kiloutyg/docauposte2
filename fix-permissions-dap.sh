#!/bin/bash

# Set permissions and ownership
chmod 750 /var/www/public/doc
chown -R www-data:www-data /var/www/public/doc
# chmod 640 /var/www/.env
# chown www-data:www-data /var/www/.env
chmod 640 /dap-certs/.env
chown www-data:www-data /dap-certs/.env
chmod 644 /dap-certs/ca-cert.pem /dap-certs/server-cert.pem
chmod 644 /dap-certs/server-key.pem
chown www-data:www-data /dap-certs/*.pem