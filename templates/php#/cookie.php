<?php
// setcookie('utilisateur', 'John', time() + 60 * 60 * 24);
$user = [
    'nom' => 'John',
    'prenom' => 'Doe',
    'age' => 30,
];

$serialized = serialize($user);
setcookie('utilisateur', $serialized, time() + 60 * 60 * 24);

$unserialized = unserialize($serialized);
$title = 'Cookie';
$nav = 'cookie';

require('header.php');

dump($user);
dump($serialized);
dump($unserialized);
dump($_COOKIE);

require('footer.php');