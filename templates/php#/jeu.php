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
/// Composer un formulaire pour demander a l'utilisateur comment composer sa glace.
/// Construire un formulaire qui permet de cochet les differentes cases et donner le prix de la glace et enfin partager la glace avec des amis.
// Checkbox
$parfum = [
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
        <form class="mt-5" action="/jeu.php" method="GET">
            <div>
                <h5>Composer votre glace</h5>


            </div>
        </form>
    </div>

    <div class="container">

        <form class="mt-5" action="/jeu.php" method="GET">
            <div>
                <h5>Composer votre glace</h5>
                <table>

                    <table mat-table #table [dataSource]="dataSource">
                        <ng-container matColumnDef="column">
                            <th mat-header-cell *matHeaderCellDef> Parfum </th>
                            <tbody>
                                <td mat-cell *matCellDef="let row">
                                    <input type="checkbox" name="parfum[]" value="fraise">Fraise<br>
                                    <input type="checkbox" name="parfum[]" value="vanille">Vanille<br>
                                    <input type="checkbox" name="parfum[]" value="chocolat">Chocolat<br>
                                </td>
                            </tbody>
                            <th mat-header-cell *matHeaderCellDef> Cornets </th>
                            <tbody>
                                <td mat-cell *matCellDef="let row">
                                    <input type="checkbox" name="cornets[]" value="pot">Pot<br>
                                    <input type="checkbox" name="cornets[]" value="cornets">Cornets<br>
                                </td>
                            </tbody>
                            <th mat-header-cell *matHeaderCellDef> Suppléments </th>
                            <tbody>
                                <td mat-cell *matCellDef="let row">
                                    <input type="checkbox" name="supplements[]" value="pepites de chocolat">Pépites
                                    de
                                    chocolat<br>
                                    <input type="checkbox" name="supplements[]" value="chantilly">Chantilly<br>
                                </td>
                            </tbody>
                        </ng-container>
                        <tr mat-header-row *matHeaderRowDef="['column']"></tr>
                        <tr mat-row *matRowDef="let row; columns: ['column'];"></tr>
                    </table>
            </div>
            <button class="btn btn-primary mt-3" type="submit">Deviner</button>
        </form>
    </div>
</div>

<div class="container">
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