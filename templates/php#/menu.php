<?php
// require_once('menu_functions.php');
if (!function_exists('nav_menu')) {
    function nav_item(string $lien, string $titre, string $linkclass = ''): string
    {
        $classe = 'nav-item';
        if ($_SERVER['SCRIPT_NAME'] === $lien) {
            $classe .= ' active';
        }
        return <<<HTML
    <li class="$classe">
        <a class="$linkclass" href="$lien">$titre</a>
    </li>
HTML;
    }

    function nav_menu($linkclass): string
    {
        return
            nav_item('/index.php', 'Accueil', $linkclass) .
            nav_item('/contact.php', 'Contact', $linkclass);
    }
}
?>
<!-- <?= nav_item('/index.php', 'Accueil', $class); ?>
<?= nav_item('/contact.php', 'Contact', $class); ?> -->