<?php
$title = 'Page de contact';
$nav = 'contact';
require 'header.php';
require_once('config.php');
$creneaux = CRENEAU;
$creneaux2 = CRENEAUX;

?>

<div class="row">
    <div class="container col-md-8">
        <h2>Nous contacter</h2>
        <div class="d-flex">
            <p> lorem ipsum </p>
        </div>
    </div>
    <div class="col-md-4">
        <h2>Horaires d'ouverture</h2>
        <div class="d-flex">
            <?= creneaux_html(CRENEAU) ?>
        </div>
        <div class="d-flex">
            <?= creneaux2_html(CRENEAU) ?>
        </div>
        <div class="d-flex">
            <?= creneaux3_html(CRENEAU) ?>
        </div>
        <div class="d-flex">
            <?= creneaux4_html(JOURS, CRENEAUX) ?>
        </div>
        </ul>
    </div>
</div>
<?php require 'footer.php'; ?>