<?php
$title = 'Menu';
require 'header.php';
require_once('functions.php');
require_once('config.php');


$nav = 'Menu';
$tsv = __DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'menu.tsv';
$csv = __DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'menu.csv';
$tsvressource = fopen($tsv, 'r');
$csvressource = fopen($csv, 'r');

$lignes = file(__DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'menu.tsv');
foreach ($lignes as $k => $ligne) {
    $lignes[$k] = explode("\t", trim($ligne));
}
$csvlignes = file(__DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'menu.csv');
foreach ($csvlignes as $k => $csvligne) {
    $csvlignes[$k] = str_getcsv(trim($csvligne, "\n\r\t\v\x00,"));
}
?>

<DOCTYPE html>
    <html lang="fr">

    <h1>
        <?php if (isset($title)) {
            echo $title;
        } else {
            echo "Mon site";
        } ?>
    </h1>
    <br><br>
    <div>
        <?php
        menu_tsv($tsv);
        ?>
    </div>
    <br><br><br><br>
    <div>
        <h1>
            Menu du tuto avec le TSV
        </h1>
        <br><br>
        <div>
            <?php foreach ($lignes as $ligne) : ?>
            <div>
                <?php if (count($ligne) === 1) : ?>
                <h2><?= $ligne[0] ?></h2>
                <?php else : ?>
                <div class="row">
                    <div class="col-sm-8">
                        <p>
                            <strong><?= $ligne[0] ?></strong><br>
                            <?= $ligne[1] ?>
                        </p>
                    </div>
                    <div class="col-sm-4">
                        <strong><?= number_format($ligne[2], 2, ',', '') ?> €</strong>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <br><br><br><br>
    <div>
        <h1>
            Menu du tuto avec le CSV
        </h1>
        <br><br>
        <div>
            <?php foreach ($csvlignes as $csvligne) : ?>
            <div>
                <?php if (count($csvligne) === 1) : ?>
                <h2><?= $csvligne[0] ?></h2>
                <?php else : ?>
                <div class="row">
                    <div class="col-sm-8">
                        <p>
                            <strong><?= $csvligne[0] ?></strong><br>
                            <?= $csvligne[1] ?>
                        </p>
                    </div>
                    <div class="col-sm-4">
                        <strong><?= number_format($csvligne[2], 2, ',', '') ?> €</strong>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <br><br><br><br>
    <footer>
        <?php

        require 'footer.php'; ?>
    </footer>