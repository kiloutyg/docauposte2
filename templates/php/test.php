<?php
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
echo "\n";
echo "\n";

if ($creneautrouve) {
    echo "Le magasin sera ouvert.";
} else {
    echo "Le magasin sera fermé.";
}
echo "\n";

echo "\n";
echo " Le magasin est ouvert de ";
echo implode('h à ', $creneaux[0]) . "h";
echo "\n";
echo "\n";

echo " Le magasin est ouvert de " . $creneaux[0][0] . "h à " . $creneaux[0][1] . "h";
echo "\n";
echo "\n";

echo " Le magasin est ouvert de ";
foreach ($creneaux as $creneau) {
    echo "{$creneau[0]}h à {$creneau[1]}h";
}
echo "\n";
echo "\n";

echo " Le magasin est ouvert de ";
foreach ($creneaux as $creneau) {
    echo $creneau[0] . "h à " . $creneau[1] . "h";
}
echo "\n";
echo "\n";

echo " Le magasin est ouvert de ";
foreach ($creneaux as $k => $creneau) {
    if ($k > 0) {
        echo " et de ";
    }
    echo "{$creneau[0]}h à {$creneau[1]}h";
}