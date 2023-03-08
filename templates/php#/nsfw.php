<?php
$title = 'Hot';
$nav = 'Hot';
$birthdate = '';
$age = '';
if (!empty($_GET['action']) && $_GET['action'] === 'deconnexion') {
    unset($_COOKIE['age']);
    setcookie('age', '', time() - 3600);
    header('Location: /profil.php');
    exit();
}


if (!empty($_COOKIE['age'])) {
    $age = $_COOKIE['age'];
}

if (!empty($_POST['birthdate'])) {
    $birthdate = $_POST['birthdate'];
    $age = date('Y') - $birthdate;
    setcookie('age', $age);
}
require 'header.php';

?>
<!--
 // demander à l'utilisateur de saisir sa date de naissance.
// persister la date de naissance dans un cookie.
// Si l'utilisateur est assez vieux, afficher le contenu.
// Sinon, afficher un messbirthdate d'erreur.
 -->


<div class="container">

    <h1>Hot</h1>
    <?php if ($age === '') : ?>
    <div class="alert alert-danger">
        <p>Vous devez entrer votre année de naissance pour consulter cette page ! </p>
    </div>
    <form action="/nsfw.php" method="post">
        <div class="container">
            <div class="container">
                <label for="birthdate">Entrer votre année de naissance : </label>
            </div>
            <div class="form-group container">
                <select class="form-control" name="birthdate" id="birthdate" required>
                    <option value=''>Année</option>
                    <?php for ($i = 2022; $i > 1919; $i--) : ?>
                    <option value="<?= $i ?>"><?= $i ?></option>
                    <?php endfor; ?>

                </select>
                <!-- <input type="date" name="birthdate" id="birthdate" class="form-control" placeholder="Entrer votre birthdate" min="1919"
                    max="2022" value="<?= htmlentities($birthdate) ?>"> -->
            </div>
            <div class="container">
                <button type="submit" class="btn btn-primary">Se connecter</button>
            </div>
        </div>
    </form>
    <?php elseif ($age >= 18) : ?>
    <div class="alert alert-success">
        <p>Bienvenu sur ton feed SeuleVentilo! </p>

    </div>
    <button style="color:pink" class="btn btn-primary"><a style="color:white" href="/nsfw.php?action=deconnexion">Se
            déconnecter</a></button>
    <?php else : ?>
    <div class="alert alert-danger">
        <p>Vous êtes trop jeunes pour le SeuleVentilo! </p>
        <button class="btn btn-primary"><a style="color:white" href="/index.php">Accueil</a></button>
    </div>


    <?php endif; ?>

    <?php
    dump($_POST);
    dump($_COOKIE);
    require 'footer.php';
    ?>