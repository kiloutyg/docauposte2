 /*
 <!-- {% extends '/php/basephp.html.twig' %}
{% block body %}
{% endblock %} -->
 */
 <?php
    /*
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
echo "\n";

echo "\nAjout d'une sous condition\n";

echo "\n";

$note5 = readline("Entrez une note : ");
if ($note5 >= 10) {
    if ($note5 == 10) {
        echo "Vous avez juste la moyenne";
    } else {
        echo "Vous avez plus que la moyenne";
    }
} else {
    echo "Vous n'avez pas la moyenne";
}

echo "\n";
echo "\nAjout d'une condition avec elseif\n";
echo "\n";

if ($note5 > 10) {
    echo "Bravo vous avez plus que la moyenne";
} elseif ($note5 == 10) {
    echo "Vous avez juste la moyenne";
} else {
    echo "Vous n'avez pas la moyenne";
}
echo "\n";
echo "\n Ajout du triple egal pour verifier la valeur et le type \n";
echo "\n";

if ($note5 > 10) {
    echo "Bravo vous avez plus que la moyenne";
} elseif ($note5 === 10) {
    echo "Vous avez juste la moyenne";
} else {
    echo "Vous n'avez pas la moyenne";
}

echo "\n";
echo "\nAjout de la conversion du type en indiquant le type entre parenthese avant la valeur pour eviter une erreur avec le triple egal. \n";
echo "\n";

$note6 = (int)readline("Entrez une note : ");
echo "\n";
if ($note6 > 10) {
    echo "Bravo vous avez plus que la moyenne";
} elseif ($note6 === 10) {
    echo "Vous avez juste la moyenne";
} else {
    echo "Vous n'avez pas la moyenne";
}

echo "\n";
echo "\n";
echo "\n";
echo "\nTest d'une collection de valeur. En specifiant bien le type de valeur dans la variable. Avec une suite de test elseif. \n";
echo "\n";

$action = (int)readline("Entrez une action (1: attaquer, 2: defendre, 3: passer mon tour : ");
if ($action === 1) {
    echo "Vous attaquez";
} elseif ($action === 2) {
    echo "Vous defendez";
} elseif ($action === 3) {
    echo "Vous passez votre tour";
} else {
    echo "Vous n'avez pas choisi une action valide";
}

echo "\n";
echo "\n";
echo "\n";
echo "\nTest d'une collection de valeur. En specifiant bien le type de valeur dans la variable. Avec un switch. \n";
echo "\n";

$action1 = (int)readline("Entrez une action (1: attaquer, 2: defendre, 3: passer mon tour) : ");

switch ($action1) {
    case 1:
        echo "Vous attaquez";
        break;
    case 2:
        echo "Vous defendez";
        break;
    case 3:
        echo "Vous passez votre tour";
        break;
    default:
        echo "Vous n'avez pas choisi une action valide";
        break;
}



    echo "\n";
    echo "\n";
    echo "\n";
    echo "\nTest pour voir si un magasin est ouvert en testant l'heure donner aux horaires d'ouverture. \n";
    echo "\n";


    $heure = (int)readline("Entrez une heure : ");

    if ($heure >= 9 && $heure <= 12 || $heure >= 14 && $heure <= 18) {
        echo "Le magasin est ouvert";
    } else {
        echo "Le magasin est fermé";
    }
   
    /*
VRAI && VRAI = VRAI
VRAI && FAUX = FAUX
FAUX && VRAI = FAUX
FAUX && FAUX = FAUX

VRAI || VRAI = VRAI
VRAI || FAUX = VRAI
FAUX || VRAI = VRAI
FAUX || FAUX = FAUX
*/
    /*
    echo "\n";
    echo "\n";
    echo "\n";

    echo "\n";
    echo "\n";
    echo "\nTest pour voir si un magasin est ouvert en testant l'heure donner aux horaires d'ouverture. En inversant les choses et en adaptant les conditions.  \n";
    echo "\n";


    $heure = (int)readline("Entrez une heure : ");

    if ($heure < 9 || $heure > 12 && $heure < 14 || $heure > 18) {
        echo "Le magasin est fermé";
    } else {
        echo "Le magasin est ouvert";
    }

    echo "\n";
    echo "\n";

    if (!($heure >= 9 && $heure <= 12 || $heure >= 14 && $heure <= 18)) {
        echo "Le magasin est fermé";
    } else {
        echo "Le magasin est ouvert";
    }
    echo "\n";
    echo "\n";
 
    echo "\n";
    echo "\n";
    echo "\nDebut sur les loop avec While.  \n";
    echo "\n";

    $chiffre = null;

    while ($chiffre !== 42) {
        $chiffre = (int)readline("Entrez un chiffre : ");
    }
    echo "Bravo vous avez trouvé le chiffre mystère";
  
    echo "\n";
    echo "\n";
    echo "\nLoop avec for.  \n";
    echo "\n";



    for ($i = 0; $i < 10; $i++) {
        echo "- $i \n";
    }
    echo "\n";
    echo "\nEn utilisant le $i = $i + 2.  \n";
    for ($i = 0; $i < 10; $i = $i + 2) {
        echo "- $i \n";
    }
    echo "\n";
    echo "\nEn utilisant le += 2.  \n";
    for ($i = 0; $i < 10; $i += 2) {
        echo "- $i \n";
    }
    echo "\n";
    echo "\nEn utilisant le -= 2 avec une seconde expression inverser à -10 plutot que 10  \n";
    for ($i = 0; $i > -10; $i -= 2) {
        echo "- $i \n";
    }
 */
    echo "\n";
    echo "\n";
    echo "\nLoop avec for pour afficher une liste complete.   \n";
    echo "\n";

    $notes = [10, 12, 8, 20, 18, 16, 14];

    for ($i = 0; $i < count($notes); $i++) {
        echo "- $notes[$i]" . "\n";
    }
    echo "\n";

    ?>