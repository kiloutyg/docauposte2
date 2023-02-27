<?php
$nom = 'jean';
function bonjour($nom)
{
    return 'Bonjour ' . $nom . "! \n";
}
$salutation = bonjour($nom);
echo $salutation;