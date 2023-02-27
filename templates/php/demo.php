<!-- {% extends '/php/basephp.html.twig' %}
{% block body %}
{% endblock %} -->

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
=========================================================================================================================

$moyenne = ($note + $note2) / 2;

echo "\nBonjour $prenom $nom vous avez eu une moyenne de $moyenne sur 20.\n";
=========================================================================================================================

echo "\n tableau unidimensionnel \n";
$notes = [12, 15, 18, 20, 10, 8, 5, 3, 0, 2, 4, 6, 7, 9, 11, 13, 14, 16, 17, 19, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30];

echo $notes[1];
=========================================================================================================================

echo "\n tableau multidimensionnel \n";

$eleve = ['jean', 'dupont', [12, 15, 18]];

echo $eleve[2][1];
=========================================================================================================================

echo "\n tableau multidimensionnel avec clef particuliere \n";

$eleves = [
    'prenom' => 'jean',
    'nom' => 'dupont',
    'notes' => [12, 15, 18]
];

echo $eleves['nom'] . ' ' . $eleves['prenom'] . ' ' . $eleves['notes'][1];
=========================================================================================================================

echo "\n Changement de valeur d'une clef \n";

$eleves['prenom'] = 'Marc';
$eleves['notes'][2] = 20;
echo $eleves['nom'] . ' ' . $eleves['prenom'] . ' ' . $eleves['notes'][2];
=========================================================================================================================


// Affichage du tableau :
echo " \n";
echo $eleves['notes'];

// Affichage du tableau avec print_r pour eviter l'echec de conversion en string avec echo:

echo "\n Obtenir plus d'info sur une variable avec print_r \n";

print_r($eleves['notes']);
=========================================================================================================================

echo "\n ajout d'un element dans le tableau \n";

$eleves['notes'][] = 10;

print_r($eleves['notes']);
=========================================================================================================================

echo "\n Ajout d'un element sans index, ce qui ajoute automatiquement un index 0 \n";

$eleves[] = 'cm-2';
print_r($eleves);

=========================================================================================================================

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

=========================================================================================================================

echo "\n afficher les informations d'un des eleves du tableau classe, specifiquement une de ses notes \n";

echo $classe[1]['notes'][1];
echo "\n";
echo "\n";

=========================================================================================================================

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

=========================================================================================================================

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

=========================================================================================================================


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


=========================================================================================================================


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

=========================================================================================================================


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


=========================================================================================================================


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


=========================================================================================================================


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


=========================================================================================================================


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




=========================================================================================================================


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

=========================================================================================================================


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


=========================================================================================================================


    echo "\n";
    echo "\n";

    if (!($heure >= 9 && $heure <= 12 || $heure >= 14 && $heure <= 18)) {
        echo "Le magasin est fermé";
    } else {
        echo "Le magasin est ouvert";
    }
    echo "\n";
    echo "\n";
 

=========================================================================================================================


    echo "\n";
    echo "\n";
    echo "\nDebut sur les loop avec While.  \n";
    echo "\n";

    $chiffre = null;

    while ($chiffre !== 42) {
        $chiffre = (int)readline("Entrez un chiffre : ");
    }
    echo "Bravo vous avez trouvé le chiffre mystère";
  

=========================================================================================================================


    echo "\n";
    echo "\n";
    echo "\nLoop avec for.  \n";
    echo "\n";



    for ($i = 0; $i < 10; $i++) {
        echo "- $i \n";
    }

=========================================================================================================================


    echo "\n";
    echo "\nEn utilisant le $i = $i + 2.  \n";
    for ($i = 0; $i < 10; $i = $i + 2) {
        echo "- $i \n";
    }
    echo "\n";

=========================================================================================================================


    echo "\nEn utilisant le += 2.  \n";
    for ($i = 0; $i < 10; $i += 2) {
        echo "- $i \n";
    }
    echo "\n";

=========================================================================================================================


    echo "\nEn utilisant le -= 2 avec une seconde expression inverser à -10 plutot que 10  \n";
    for ($i = 0; $i > -10; $i -= 2) {
        echo "- $i \n";
    }


=========================================================================================================================


echo "\n";
echo "\n";
echo "\nLoop avec for pour afficher une liste complete.   \n";
echo "\n";

$notes = [10, 12, 8, 20, 18, 16, 14];

for ($i = 0; $i < count($notes); $i++) {
    echo "- $notes[$i]" . "\n";
}
echo "\n";
 

=========================================================================================================================



echo "\n";
echo "\n";
echo "\nLoop avec foreach pour faire de l'exploration de tableau.   \n";
echo "\n";

$notes = [10, 12, 8, 20, 18, 16, 14];

echo "\nPremiere solution, en utilisant for pour faire lister toutes les notes.   \n";

for ($i = 0; $i < count($notes); $i++) {
    echo "- $notes[$i]" . "\n";
}

=========================================================================================================================


echo "\n";
echo "\nSeconde solution, en utilisant foreach pour faire lister toutes les notes. En les listant as Note pour chacune des valeurs.  \n";
echo "\n";
foreach ($notes as $note) {
    echo "- $note" . "\n";
}

=========================================================================================================================


echo "\n";
echo "\nCette boucle peut recevoir un deuxieme parametre pour afficher la clef de la valeur.  \n";
echo "\n";
$eleves = [
    'cm2' => 'Jean',
    '6eme' => 'Pierre',
    'cm1' => 'Paul',
];
echo "\n";
foreach ($eleves as $classe => $eleve) {
    echo "- $eleve est en $classe" . "\n";
}


=========================================================================================================================


echo "\n";
echo "\nCette boucle peut aussi explorer des données complexe types tableau contenant les eleves. En faisant des boucles dans les boucles.   \n";
echo "\n";
*/
/* Objectif : 
    la classe cm2: 
        - Jean
        - Jacques
        - Marie
    la classe 6eme:
        - Pierre
        - Paul
        - Jacques
        etc...
        

$eleves = [
    'cm2' => ['Jean', 'Jacques', 'Marie'],
    '6eme' => ['Pierre', 'Paul', 'Jacques'],
    'cm1' => ['Paul', 'Pierre', 'Marie'],
];


=========================================================================================================================


echo "\n";
foreach ($eleves as $classe => $ListeEleves) {
    echo "Les eleves de la classe de $classe sont : " . "\n";
    foreach ($ListeEleves as $eleve) {
        echo "- $eleve" . "\n";
    }
    echo "\n";
}



=========================================================================================================================


echo "\n";
echo "\n";
echo "\nFaire un algo qui permet d'entrée les notes d'un eleve et de les afficher. ( d'un ensemble  de notes et de les afficher)  \n";
echo "\n";
$notes = [];
$note = null;
$nb = 0;
*/
/*
//on demande une note en indiquant les conditions de fin de boucle. 
TANT QUE la note est differente de 0, on continue de demander une note.
    //on ajoute la note dans le tableau et on continue tant que le user ne tape pas 0 ou equivalent.
POUR chaque note dans le tableau, on affiche la note et on augment le nombre de notes totales.
    //Enfin on affiche le nombre de notes totales puis les notes sous formes de listes.


$note = (int)readline("Entrez les notes puis enfin entrer 0 lorsque vous avez fini : ");
echo "\n";

while ($note !== 0) {
    $notes[] = $note;
    $nb = $nb + 1;
    $note = (int)readline("Entrez la note suivante : ");
}

=========================================================================================================================


echo "\n";
echo "\n $nb notes ont été entrées : \n";

foreach ($notes as $note) {
    echo "- $note" . "\n";
}
*/
/* CORRECTION DE L'EXERCICE SELON LE TUTO : 

$notes = [];
$action = null;

//TANT QUE l'utilisateur ne tape pas "fin"
while ($action !== 'fin') {
    $action = readline('Entrer une nouvelle note (ou \'fin\' pour terminer la saisie) : ');
    // On ajoute la note tapée au tableau notes
    if ($action !== 'fin') {
        $notes[] = (int)$action;
    }
}
// POUR CHAQUE note dans le tableau notes
foreach ($notes as $note) {
    // On affiche la note
    echo "- $note" . "\n";
}


=========================================================================================================================


///Autre solution avec while(true) et break pour sortir de la boucle :\\\
   
$notes = [];
$action = null;

//TANT QUE l'utilisateur ne tape pas "fin"
while (true) {
    $action = readline('Entrer une nouvelle note (ou \'fin\' pour terminer la saisie) : ');
    // On ajoute la note tapée au tableau notes
    if ($action === 'fin') {
        break;
        
        $notes[] = (int)$action;
    }
}

=========================================================================================================================


// POUR CHAQUE note dans le tableau notes
foreach ($notes as $note) {
    // On affiche la note
    echo "- $note" . "\n";
} 


=========================================================================================================================


/*
On veut demander à l'utilisateur de rentrer les horaires d'ouverture d'un magasin. 
(entrer l'heure de debut et de fin et lui demander si il veut ajouter une nouvelle plage horaire)

On demande à l'utilisateur de rentrer une heure et on lui dira si le magasin est ouvert sous cette forme : 
    
$heure = (int)readline("Entrez une heure : ");

if ($heure < 9 || $heure > 12 && $heure < 14 || $heure > 18) {
    echo "Le magasin est fermé";
} else {
    echo "Le magasin est ouvert";
}
TANT QUE $heure n'est pas egal a zero  on continue a demander des plages horaires. 
    //On demande à l'utilisateur l'heure de debut, la deuxieme heure puis la troisieme heure et ainsi de suite jusqu'a ce que l'utilisateur tape 0.
    Les heures ne peuvent pas etre superieur a 24 et inferieur a 0 et ne peuvent etre qu'en couple de deux avec une heure de debut et une heure de fin de plages.



$heures = [];
$heure = null;
$nb = null;
$plage = null;
echo "\n";

$nb = (($plage = (int)readline("\nEntrez le nombre de plages horaires : \n")) * 2);

// echo $nb;



while ($nb !== 0) {
    while ($heure !== 0) {
        $heure = (int)readline("Entrez l'heure de debut de la plage horaire : ");
        if ($heure < 0 || $heure > 24) {
            echo "L'heure de debut ne peut pas etre inferieur a 0 ou superieur a 24";
        }
        $heures[] = $heure;
        $nb = $nb - 1;
        $heure = (int)readline("Entrez l'heure de fin de la plage horaire : ");
        if ($heure < 0 || $heure > 24) {
            echo "L'heure de fin ne peut pas etre inferieur a 0 ou superieur a 24";
        }
        $heures[] = $heure;
        $nb = $nb - 1;
        if ($heures[0] >= $heures[1]) {
            echo "\nL'heure de debut ne peut pas etre superieur ou egale à l'heure de fin.\n\n";
            $heure === null;
            echo "L'heure de debut ne peut pas etre superieur ou egale à l'heure de fin. \nEssaye encore: \n\n";
        }
        $heure = $nb;
    }
}

echo " Il y a $plage plages dont les horaires sont : \n";
foreach ($heures as $heure) {
    echo "- $heure" . "h\n";
}

echo " Il y a $plage plages dont les horaires sont : \n";
while ($plage !== 0) {
    foreach ($heures as $heure) {
        echo "- $heure" . "h\n";
    }
    $plage = $plage - 1;
}

/* =========================================================================================================================

CORRECTION DE L'EXERCICE SELON LE TUTO :

On veut deùander à mon utilisateur de rentrer les horaires d'ouverture d'un magasin.
On lui demande de rentrer une heure et on lui dira si le magasin est ouvert. 

// On demande à l'utilisateur de rentrer un creneaux 
  // On demande l'heure de debut
  // On demande l'heure de fin
  // On verifie que l'heure de debut est inferieur à l'heure de fin. 
  // On demande si l'utilisateur veut ajouter un nouveau creneaux (o/n)
// On demande à l'utilisateur de rentrer une heure
// On affiche l'état d'ouverture du magasin. 

$creneaux = [];

while (true) {
    $debut = (int)readline('Heure d\'ouverture: ');
    $fin = (int)readline('Heure de fermeture: ');
    if ($debut >= $fin) {
        echo "Le créneaux ne peut pas être enregistré car l'heure d'ouverture ($debut) est supérieur à l'heure de fermeture ($fin).";
    } else {
        $creneaux[] = [$debut, $fin];
        $action = readline('Voulez-vous enregistré un nouveau créneau (o/n) ? ');
        if ($action === 'n') {
            break;
        }
    }
};

print_r($creneaux);

$heure = (int)readline("A quelle heure voulez-vous visiter le magasin ? ");
$creneautrouve = false;

foreach ($creneaux as $creneau) {
    if ($heure >= $creneau[0] && $heure <= $creneau[1]) {
        $creneautrouve = true;
        break;
    }
}

if ($creneautrouve) {
    echo "Le magasin sera ouvert.";
} else {
    echo "Le magasin sera fermé.";
}

// Deuxieme exercice dans ce style à la difference que l'on decide de directement afficher les horaires d'ouverture du magasin.

On veut deùander à mon utilisateur de rentrer les horaires d'ouverture d'un magasin.

// On demande à l'utilisateur de rentrer un creneaux 
  // On demande l'heure de debut
  // On demande l'heure de fin
  // On verifie que l'heure de debut est inferieur à l'heure de fin. 
  // On demande si l'utilisateur veut ajouter un nouveau creneaux (o/n)
// On demande à l'utilisateur de rentrer une heure
// On affiche l'état d'ouverture du magasin. 

$creneaux = [];

while (true) {
    $debut = (int)readline('Heure d\'ouverture: ');
    $fin = (int)readline('Heure de fermeture: ');
    if ($debut >= $fin) {
        echo "Le créneaux ne peut pas être enregistré car l'heure d'ouverture ($debut) est supérieur à l'heure de fermeture ($fin).";
    } else {
        $creneaux[] = [$debut, $fin];
        $action = readline('Voulez-vous enregistré un nouveau créneau (o/n) ? ');
        if ($action === 'n') {
            break;
        }
    }
};

print_r($creneaux);

$heure = (int)readline("A quelle heure voulez-vous visiter le magasin ? ");
$creneautrouve = false;

foreach ($creneaux as $creneau) {
    if ($heure >= $creneau[0] && $heure <= $creneau[1]) {
        $creneautrouve = true;
        break;
    }
}

if ($creneautrouve) {
    echo "Le magasin sera ouvert.";
} else {
    echo "Le magasin sera fermé.";
}

/// Exemple : le magasin est ouvert de 14h à 18h et de 9h à 12h.

echo "\n";
echo " Le magasin est ouvert de ";
echo implode('h à ', $creneaux[0]) . "h";

echo " Le magasin est ouvert de " . $creneaux[0][0] . "h à " . $creneaux[0][1] . "h";

// Avec echo implode on peut afficher les horaires du magasin. 
?>