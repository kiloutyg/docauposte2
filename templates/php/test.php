<?php
$insultes = ['merde', 'connard', 'pute', 'salope', 'enculé', 'enculée', 'con', 'conne', 'connasse', 'conasse', 'conard', 'conasse', 'conne', 'conas'];

$text = readline('Entrez un texte : ');

$etoile = [];

foreach ($insultes as $insulte) {
    $etoile[] = substr_replace($insulte, str_repeat('*', strlen($insulte) - 1), 1);
}

foreach ($insultes as $insulte) {
    $text = str_replace($insultes, $etoile, $text);
};
print_r($text);