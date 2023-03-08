<?php
$title = 'Newsletter';
require 'header.php';
require_once('functions.php');
require_once('config.php');
$nav = 'Newsletter';

$error = null; // variable pour stocker les erreurs
$success = null; // variable pour stocker les succès

$newfilename = date("Y-m-d") . '.tsv'; // nom du fichier
$newfile = __DIR__ . DIRECTORY_SEPARATOR . 'emails' . DIRECTORY_SEPARATOR . "$newfilename"; // chemin du fichier

$ressource = fopen($newfile, 'ab'); // ouverture du fichier

if (!empty($_POST['email'])) { // si le formulaire est soumis
    $email = $_POST['email']; // récupération de l'email
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $emails = filter_var($email, FILTER_VALIDATE_EMAIL);
        file_put_contents($newfile, "\n$emails . PHP_EOL", FILE_APPEND);
        $success = 'Votre email a bien été enregistré'; // filtrage de l'email
        $_POST['email'] = null;
        $email = '';
        $emails = null;
    } else {
        $error = 'Email invalide'; // message d'erreur
    }
} else {
    $_POST['email'] = null;
    $email = '';
    $emails = null;
}

// $email = $_POST['email'];
// $emails = filter_var($email, FILTER_VALIDATE_EMAIL);

?>
<h2>Newsletter</h2>

<?php if ($error) : ?>
<div class="alert alert-danger">
    <?= $error ?>
</div>
<?php endif; ?>

<form action="" method="POST" class="form-inline">
    <div class="form-group">
        <input type="email" name="email" placeholder="Votre email" required class="form-control"
            value="<?= htmlentities($email) ?>">
    </div>

    <button type=" submit" class="btn btn-primary">Envoyer</button>
</form>

<?php

// file_put_contents($newfile, "\n$emails", FILE_APPEND);
// fwrite($ressource, "\n$emails");
?>

<?php if ($success) : ?>
<div class="alert alert-success">
    <?= $success ?>
</div>
<?php endif; ?>


<footer>
    <?php
    require('footer.php');
    ?>
</footer>