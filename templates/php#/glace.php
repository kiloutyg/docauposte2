<?php
require_once 'header.php';
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

// foreach (['parfums', 'supplements', 'cornets'] as $name) {
//     if (isset($_GET[$name])) {
//         if (is_array(($_GET[$name]))) {
//             foreach ($_GET[$name] as $value) {
//                 $ingredients[] = $value;
//                 $total += $$name[$value];
//                 $ingredientsprices[] = $$name[$value];
//             }
//         }
//         if (is_string(($_GET[$name]))) {
//             $ingredients[] = ($_GET[$name]);
//             $total += $$name[($_GET[$name])];
//             $ingredientsprices[] = $$name[($_GET[$name])];
//         }
//     }
// }
foreach (['parfums', 'supplements', 'cornets'] as $name) {
    if (isset($_GET[$name])) {
        $choix = $_GET[$name];
        if (is_array($choix)) {
            foreach ($choix as $value) {
                $ingredients[] = $value;
                $total += $$name[$value];
                $ingredientsprices[] = $$name[$value];
            }
        }
        if (is_string($choix)) {
            $ingredients[] = ($choix);
            $total += $$name[$choix];
            $ingredientsprices[] = $$name[$choix];
        }
    }
}

// if (isset($_GET['parfums'])) {
//     foreach ($_GET['parfums'] as $parfum) {
//         $ingredients[] = $parfum;
//         $total += $parfums[$parfum];
//         $ingredientsprices[] = $parfums[$parfum];
//     }
// }

// if (isset($_GET['supplements'])) {
//     foreach ($_GET['supplements'] as $supplement) {
//         $ingredients[] = $supplement;
//         $total += $supplements[$supplement];
//         $ingredientsprices[] = $supplements[$supplement];
//     }
// }
// if (isset($_GET['cornets'])) { {
//         $ingredients[] = ($_GET['cornets']);
//         $total += $cornets[($_GET['cornets'])];
//         $ingredientsprices[] = $cornets[($_GET['cornets'])];
//     }
// }
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
                        <p><strong>
                                <?= $total ?> €
                            </strong>
                        </p>
                    </mat-list-item>
                </mat-list>
            </ul>
        </div>
    </div>
    <div class="col-md-8 ">
        <form class="mt-5 " action="/glace.php" method="GET">
            <div>
                <h5 class="mt-2">Choisissez vos Parfums</h5>
                <?php foreach ($parfums as $parfum => $price) : ?>
                <div class="checkbox mb-2">
                    <tr>
                        <?= checkbox('parfums', $parfum, $_GET) ?>
                        <?= $parfum ?> - <?= $price ?> €
                    </tr>
                </div>
                <?php endforeach; ?>
                <h5 class="mt-2">Choisissez votre cornet</h5>
                <?php foreach ($cornets as $cornet => $price) : ?>
                <div class="checkbox mb-2">
                    <tr>
                        <?= radio('cornets', $cornet, $_GET) ?>
                        <?= $cornet ?> - <?= $price ?> €
                    </tr>
                </div>
                <?php endforeach; ?>
                <h5 class="mt-2">Choisissez vos Suppléments</h5>
                <?php foreach ($supplements as $supplement => $price) : ?>
                <div class="checkbox mb-2">
                    <tr>
                        <?= checkbox('supplements', $supplement, $_GET) ?>
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