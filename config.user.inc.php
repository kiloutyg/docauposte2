<?php

require '/etc/phpmyadmin/config.secret.inc.php';

$cfg['Servers'][$i]['auth_type'] = 'http';


/* Ensure that the file is being accessed through phpMyAdmin */
if (!defined('PHPMYADMIN')) {
    exit;
}

/* Server parameters */
$cfg['Servers'][1]['host'] = 'database';
$cfg['Servers'][1]['ssl'] = true;
$cfg['Servers'][1]['ssl_ca'] = '/etc/phpmyadmin/ssl/ca-cert.pem';
$cfg['Servers'][1]['ssl_verify'] = false; // Set to true if you want to verify the server certificate