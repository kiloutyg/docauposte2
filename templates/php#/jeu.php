<?php
$title = 'Jeux';
$aDeviner = 150;
$success = null;
$error = null;
$value = null;
if (isset($_POST['chiffre'])) {
    if ($_POST['chiffre'] > $aDeviner) {
        $error = "C'est moins";
    } elseif ($_POST['chiffre'] < $aDeviner) {
        $error = "C'est plus";
    } else {
        $success = "Bravo, vous avez bien trouvé le bon numéro : <strong>$aDeviner</strong> ";
    }
    $value = (int)$_POST['chiffre'];
}
require('header.php');
?>

<!doctype html>
<html lang="fr">



<body>
    <div class="mb-3">
        <h5> Devinez le nombre</h5>
    </div>
</body>
<div class="mb-1">
    <?php if ($error) : ?>
    <div class="alert alert-danger">
        <?= $error ?>
    </div>
    <?php elseif ($success) :  ?>
    <div class="alert alert-success">
        <?= $success ?>
    </div>
    <?php endif; ?>
    <div class="container">
        <form action="/jeu.php" method="POST">
            <div class="form-group">
                <input type="number" class="form-control" name="chiffre" placeholder="Entrez un nombre entre 0 et 1000 
        " value="<?= $value ?>">
            </div>
            <button class="btn btn-primary mt-3" type="submit">Deviner</button>
        </form>
    </div>
    <div class="container">
        <form class="mt-5" action="/jeu.php">
            <div>
                <input type="checkbox" name="parfum" value="fraise">Fraise<br>
                <input type="checkbox" name="parfum" value="vanille">Vanille<br>
                <input type="checkbox" name="parfum" value="chocolat">Chocolat<br>
            </div>
            <button class="btn btn-primary mt-3" type="submit">Deviner</button>
        </form>
    </div>
</div>

<div>
    <pre>
        <h5>$_POST</h5>
        <?php
        var_dump($_POST);
        ?>
        <h5>$_GET</h5>
        <?php
        var_dump($_GET);
        ?>
    </pre>
</div>


<footer>
    <?php
    require('footer.php');
    ?>
</footer>