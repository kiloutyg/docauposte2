<?php
$title = 'Page de contact';
$nav = 'contact';
require 'header.php';
require_once('config.php');

date_default_timezone_set('CET');
$today = (int)Date('N') - 1;
$heure = (int)Date('H');
$creneaux = CRENEAUX[$today];
$ouvert = in_creneaux($heure, $creneaux);

$checkheure = (int)($_GET['heure'] ?? Date('H'));
// if (isset($_GET['heure'])) {
//     $checkheure = (int)$_GET['heure'];
// } else {
//     $checkheure = (int)$heure;
// }

$checkjour = (int)($_GET['jour'] ?? Date('N') - 1);
// if (isset($_GET['jour'])) {
//     $checkjour = (int)$_GET['jour'];
// } else {
//     $checkjour = (int)$today;
// }

$ouverture = in_creneaux($checkheure, CRENEAUX[$checkjour]);
?>
<?php

dump($checkjour);
dump($checkheure);
?>
<div class="row">
    <div class="container col-md-9">
        <h2>Nous contacter</h2>
        <div class="d-flex">
            <p> lorem ipsum </p>
        </div>

    </div>
    <div class="container col-md-9">
        <br>
        <br>
        <form action="" method="GET">
            <div class="form-group">
                <label>Choissisez le jour de votre visite :</label>
                <?= select('jour', $checkjour, JOURS) ?>
                <!-- <select class="form-control" name="jour" id="jour">
                    <?php foreach (JOURS as $k => $jour) : ?>
                    <option value="<?= $k ?>"><?= $jour ?></option>
                    <?php endforeach ?>
                </select> -->
            </div>
            <div class="form-group">

                <label>Choissisez l'heure de votre visite :</label>
                <input class="form-control" type="number" name="heure" id="heure" value="<?= $heure ?>">
            </div>
            <button type="submit" class="btn btn-primary">Vérifier les horaires d'ouverture</button>
        </form>
        <br>
        <br>
        <div class="">
            <?= creneaux8_html($ouverture) ?>
        </div> <br>
        <br>
        <div class="">
            <?= creneaux7_html(JOURS, CRENEAUX, $ouvert) ?>
        </div>
    </div>


    <br>
    <br>
    <div class="container col-md-9">

        <br>
        <br>
        <h2>Horaires d'ouverture</h2>
        <div class="d-flex">
            <?= creneaux_html(CRENEAU) ?>
        </div>
        <br>
        <br>
        <div class="d-flex">
            <?= creneaux2_html(CRENEAU) ?>
        </div>
        <br>
        <br>
        <div class="d-flex">
            <?= creneaux3_html(CRENEAU) ?>
        </div>
        <br>
        <br>
        <div class="d-flex">
            <ul><?php foreach (JOURS as $k => $jour) : ?>
                <li <?php if ($k + 1 === (int) date('N')) : ?> style="color:green" <?php endif ?>>
                    <strong><?= $jour ?></strong> : <?= creneaux3_html(CRENEAUX[$k]) ?>
                </li>
                <?php endforeach ?>
            </ul>
        </div>
        <div class="">
            <?= creneaux4_html(JOURS, CRENEAUX) ?>
        </div>
        <br>
        <br>
        <div class="">
            <?= creneaux5_html(JOURS, CRENEAUX) ?>
        </div>
        <br>
        <br>
        <div class="">
            <?= creneaux6_html(JOURS, CRENEAUX) ?>
        </div>
        <br>
        <br>
        <div class="">
            <?php var_dump(date("Y-m-d G:i:s")); ?>
            <?php var_dump($today); ?>
            <?php var_dump($heure); ?>
            <?php var_dump(in_creneaux($heure, $creneaux)); ?>
        </div>
        <br>
        <br>
        <div class="">
            <?= creneaux7_html(JOURS, CRENEAUX, $ouvert) ?>
        </div>
    </div>
</div>
<?php require 'footer.php'; ?>