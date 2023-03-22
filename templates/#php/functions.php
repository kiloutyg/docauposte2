<?php

function repondre_oui_non($question)
{
    while (true) {
        $reponse = readline($question . ' (oui/non) ');
        if ($reponse == 'oui' || $reponse == 'o') {
            return true;
        } elseif ($reponse == 'non' || $reponse == 'n') {
            return false;
        }
        echo "Réponse incorrecte ! Veuillez répondre par oui ou non.\n";
        return repondre_oui_non($question);
    }
}

// $resultat = repondre_oui_non('Voulez-vous continuer ?');
// var_dump($resultat);


function demander_creneau($question = 'Veuillez entrer un creneau ')
{
    echo $question . ": \n";
    while (true) {
        $reponsedebut = (int)readline('Heure de debut : ');
        $reponsefin = (int)readline('Heure de fin : ');
        if (($reponsedebut < 0 || $reponsedebut > 23) || ($reponsefin < 0 || $reponsefin > 23) || ($reponsefin <= $reponsedebut)) {
            echo "Réponse incorrecte ! Veuillez répondre par un horaire entre 0h et 23h et un horaire de debut précedant celui de fin.\n";
            return demander_creneau();
        }
        return [$reponsedebut, $reponsefin];
    }
}

// $creneau = demander_creneau();
// $creneau2 = demander_creneau('Veuillez entrer votre creneau horaire ');
// var_dump($creneau, $creneau2);

function demander_creneaux($phrase = 'Veuillez entrer vos creneaux horaires ')
{
    echo $phrase . ": \n";
    $creneaux = [];
    while (true) {
        $creneaux[] = demander_creneau();
        $reponse = repondre_oui_non('Voulez-vous ajouter un autre creneau ?');
        if ($reponse == false) {
            return $creneaux;
        }
    }
}

// $creneaux = demander_creneaux('Entrez vos creneaux horaires ');
// var_dump($creneaux);
// print_r($creneaux);