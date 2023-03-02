<?php
require 'header.php';
$title = 'Composer votre Glace';

/// Composer un formulaire pour demander a l'utilisateur comment composer sa glace.
/// Construire un formulaire qui permet de cochet les differentes cases et donner le prix de la glace et enfin partager la glace avec des amis.
// Checkbox
$parfums = [
    'Fraise' => 4,
    'Chocolat' => 5,
    'Vanille' => 3
];
// Radio (pas de value car une seule valeur possible)
$cornets = [
    'Pot' => 2,
    'Cornet' => 3
];
//Checkbox
$supplements = [
    'Pépites de chocolat' => 1,
    'Chantilly' => 0.5
];
$ingredients = [];
$ingredientsprices = [];
$total = 0;
if (isset($_GET['Parfums'])) {
    foreach ($_GET['Parfums'] as $parfum) {
        $ingredients[] = $parfum;
        $total += $parfums[$parfum];
        $ingredientsprices[] = $parfums[$parfum];
    }
}

if (isset($_GET['Cornets'])) { {
        $ingredients[] = ($_GET['Cornets']);
        $total += $cornets[($_GET['Cornets'])];
        $ingredientsprices[] = $cornets[($_GET['Cornets'])];
    }
}
if (isset($_GET['Suppléments'])) {
    foreach ($_GET['Suppléments'] as $supplement) {
        $ingredients[] = $supplement;
        $total += $supplements[$supplement];
        $ingredientsprices[] = $supplements[$supplement];
    }
}
?>

<!doctype html>
<html lang="fr">
<div class="d-flex align-center">
    <h1><?= $title ?></h1>
</div>

<div class="d-flex row ">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Votre Glace</h5>
            </div>
            <ul>
                <mat-list>
                    <mat-list-item>
                        <?php foreach ($ingredients as $ingredient) : ?>
                        <li><?= $ingredient ?></li>
                        <?php endforeach; ?>
                        <?= $total ?> €
                    </mat-list-item>
                </mat-list>
            </ul>
        </div>
    </div>
    <div class="col-md-8 ">
        <form class="mt-5 " action="/glace.php" method="GET">
            <div>
                <h5 class="mt-2">Choisissez vos parfums</h5>
                <?php foreach ($parfums as $parfum => $price) : ?>
                <div class="checkbox mb-2">
                    <tr>
                        <?= checkbox('Parfums', $parfum, $_GET) ?>
                        <?= $parfum ?> - <?= $price ?> €
                    </tr>
                </div>
                <?php endforeach; ?>
                <h5 class="mt-2">Choisissez votre cornet</h5>
                <?php foreach ($cornets as $cornet => $price) : ?>
                <div class="checkbox mb-2">
                    <tr>
                        <?= radio('Cornets', $cornet, $_GET) ?>
                        <?= $cornet ?> - <?= $price ?> €
                    </tr>
                </div>
                <?php endforeach; ?>
                <h5 class="mt-2">Choisissez vos suppléments</h5>
                <?php foreach ($supplements as $supplement => $price) : ?>
                <div class="checkbox mb-2">
                    <tr>
                        <?= checkbox('Suppléments', $supplement, $_GET) ?>
                        <?= $supplement ?> - <?= $price ?> €
                    </tr>
                </div>
                <?php endforeach; ?>
                <button class="mt-3 btn btn-primary" type="submit">Choisir</button>
            </div>
        </form>
    </div>
</div>
<div class="">
    <pre>
        <h5>ingredients</h5>
        <?php
        var_dump($ingredients);
        ?>
        <h5>ingredientsPrices</h5>
        <?php
        var_dump($ingredientsprices);
        ?>
        <h5>total</h5>
        <?php
        var_dump($total);
        ?>
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

<?php
require 'footer.php';
?>