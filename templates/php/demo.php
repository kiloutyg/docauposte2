<!-- {% extends '/php/basephp.html.twig' %}
{% block body %}
{% endblock %} -->

<?php
$prenom = 'Jean';
$nom = 'Dupont';
$note = 12;
$note2 = 15;


echo 'Bonjour ' . $prenom . ' ' . $nom .  ' vous avez eu ' . $note .  ' et ' . $note2 . ' sur 20.';

echo "\nBonjour $prenom $nom vous avez eu $note et $note2 sur 20.\n";

echo "\nBonjour {$prenom} {$nom} vous avez eu une moyenne de " . (($note + $note2) / 2) . " sur 20.\n";

echo "\n", 'Bonjour ' . $prenom . ' ' . $nom .  ' vous avez eu ' . (($note + $note2) / 2) . ' sur 20.';

$moyenne = ($note + $note2) / 2;

echo "\nBonjour $prenom $nom vous avez eu une moyenne de $moyenne sur 20.\n";
echo "\n tableau unidimensionnel \n";
$notes = [12, 15, 18, 20, 10, 8, 5, 3, 0, 2, 4, 6, 7, 9, 11, 13, 14, 16, 17, 19, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30];

echo $notes[1];
echo "\n tableau multidimensionnel \n";

$eleve = ['jean', 'dupont', [12, 15, 18]];

echo $eleve[2][1];

echo "\n tableau multidimensionnel avec clef particuliere \n";

$eleves = [
    'prenom' => 'jean',
    'nom' => 'dupont',
    'notes' => [12, 15, 18]
];

echo $eleves['nom'] . ' ' . $eleves['prenom'] . ' ' . $eleves['notes'][1];

echo "\n Changement de valeur d'une clef \n";

$eleves['prenom'] = 'Marc';
$eleves['notes'][2] = 20;
echo $eleves['nom'] . ' ' . $eleves['prenom'] . ' ' . $eleves['notes'][2];
// Affichage du tableau :
echo " \n";
echo $eleves['notes'];

echo "\n Obtenir plus d'info sur une variable avec print_r \n";

print_r($eleves['notes']);

echo "\n ajout d'un element dans le tableau \n";

$eleves['notes'][] = 10;

print_r($eleves['notes']);

echo "\n Ajout d'un element sans index, ce qui ajoute automatiquement un index 0 \n";

$eleves[] = 'cm-2';
print_r($eleves);

echo "\n tableau qui contient des tableaux \n";

$classe = [
    [
        'prenom' => 'jean',
        'nom' => 'dupont',
        'notes' => [12, 15, 18]
    ],
    [
        'prenom' => 'marc',
        'nom' => 'dupond',
        'notes' => [12, 15, 18]
    ],
    [
        'prenom' => 'pierre',
        'nom' => 'dupont',
        'notes' => [12, 15, 18]
    ]
];
echo "\n afficher les informations d'un des eleves du tableau classe, specifiquement une de ses notes \n";

echo $classe[1]['notes'][1];
echo "\n";
echo "\n";

echo "\nAjout de logique avec les conditions : \n";
echo "\n";

$note3 = 8;


if ($note >= 10) {
    echo "Vous avez la moyenne";
} else {
    echo "Vous n'avez pas la moyenne";
}
echo "\n";
if ($note3 >= 10) {
    echo "Vous avez la moyenne";
} else {
    echo "Vous n'avez pas la moyenne";
}

echo "\n";
echo "\n Ajout d'une readline pour effectuer une entrée de donnée \n";
$note4 = readline("Entrez une note : ");
echo "\n";
echo "\n";
if ($note4 >= 10) {
    echo "Vous avez la moyenne";
} else {
    echo "Vous n'avez pas la moyenne";
}
?>