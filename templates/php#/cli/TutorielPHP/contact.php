<?php 
$title = "Nous contacter";
require_once 'data/config.php';
require_once 'functions.php';
date_default_timezone_set('Europe/Paris');
$heure = (int)($_GET['heure'] ?? date('G'));
$jour = (int)($_GET['jour'] ?? date('N') - 1);
$creneaux = CRENEAUX[$jour];
$ouvert = in_creneaux($heure, $creneaux);
$color = $ouvert ? 'green' : 'red';
require 'elements/header.php'; 
?>

<div class="row">
    <div class="col-md-8">
    <h2>Nous contacter</h2>
    <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Quibusdam eveniet similique blanditiis iusto voluptas illo accusantium aperiam quas mollitia! Nobis optio provident tenetur ad ipsam quibusdam impedit voluptatibus doloribus rerum.</p>
    </div>
    <div class="col-md-4">
        <h2>Horaire d'ouvertures</h2>

        <?php if ($ouvert): ?>
            <div class="alert alert-success">
                Le magasin sera ouvert
            </div>
        <?php else: ?>
            <div class="alert alert-danger">
                Le magasin sera fermé
            </div>
        <?php endif ?>

        <form action="" method="GET">
            <div class="from-group">
                <?= select('jour', $jour, JOURS) ?>
            </div>
            <div class="form-group">
                <input class="form-control" type="number" name="heure" value="<?= $heure ?>">
            </div>
            <button class="btn btn-primary" type="submit">Voir si le magasin est ouvert</button>
        </form>

        <ul>
            <?php foreach(JOURS as $k => $jour): ?>
                <li>
                    <strong><?= $jour ?></strong> :
                    <?= creneaux_html(CRENEAUX[$k]); ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>


<?php require 'elements/footer.php'; ?>