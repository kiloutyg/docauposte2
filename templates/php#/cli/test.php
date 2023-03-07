<?php
// var_dump(__DIR__);
// var_dump(dirname(__DIR__));
// $fichier = __DIR__ . DIRECTORY_SEPARATOR . 'demo.txt';
// // file_put_contents($fichier, 'Hello World');
// file_put_contents($fichier, 'Hello World', FILE_APPEND);

// file_get_contents($fichier); 

$fichier = __DIR__ . DIRECTORY_SEPARATOR . 'TutorielPHP' .  DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'menu.csv';

// var_dump($fichier);

// $ressource = fopen($fichier, 'r');

// var_dump($ressource);

// echo file_get_contents($fichier);

// echo fread($ressource, 16);

// $ligne = fgets($ressource);

// var_dump(fstat($ressource));

// var_dump(fgets($ressource));
// var_dump(fgets($ressource));
// $k = 0;
// while ($ligne = fgets($ressource)) {
//     $k++;
//     if ($k === 12) {
//         echo $ligne;
//         break;
//     }
// }
// fclose($ressource);

// $ressource = fopen($fichier, 'r+');
// $k = 0;
// while ($ligne = fgets($ressource)) {
//     $k++;
//     if ($k === 0) {
//         fwrite($ressource, 'Hello World');
//         break;
//     }
// }
// fclose($ressource);

$ressource = fopen($fichier, 'r+');

echo fgets($ressource);
echo fread($ressource, 13);

fclose($ressource);