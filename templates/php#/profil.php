<?php
$title = 'profile';
$nav = 'profile';
$nom = '';
// if (!empty($_GET['action']) && $_GET['action'] === 'deconnexion') {
//     setcookie('utilisateur', '', time() - 3600);
//     header('Location: /profil.php');
//     exit();
// }

if (!empty($_GET['action']) && $_GET['action'] === 'deconnexion') {
    unset($_COOKIE['utilisateur']);
    setcookie('utilisateur', '', time() - 3600);
    header('Location: /profil.php');
    exit();
}

if (!empty($_COOKIE['utilisateur'])) {
    $nom = $_COOKIE['utilisateur'];
}
if (!empty($_POST['nom'])) {
    setcookie('utilisateur', $_POST['nom']);
    $nom = $_POST['nom'];
}
require('header.php');
?>

<div class="container">
    <h1>Profil</h1>
    <?php if ($nom) : ?>
    <div class="alert alert-success">
        <p>Bonjour <?= $nom ?></p>

    </div>
    <button class="btn btn-primary"><a style="color:white" href="/profil.php?action=deconnexion">Se
            déconnecter</a></button>
    <?php else : ?>
    <div class="alert alert-danger">
        <p>Vous n'êtes pas connecté</p>
    </div>


    <form action="/profil.php" method="post">
        <div class="container">
            <div class="container">
                <label for="nom">Entrer votre nom : </label>
            </div>
            <div class="form-group container">
                <input type="nom" name="nom" id="nom" class="form-control" placeholder="Entrer votre nom"
                    value="<?= htmlentities($nom) ?>">
            </div>
            <div class="container">
                <button type="submit" class="btn btn-primary">Se connecter</button>
            </div>
        </div>
    </form>
    <?php endif; ?>
</div>
<?php
var_dump($_COOKIE);

require('footer.php');
?>