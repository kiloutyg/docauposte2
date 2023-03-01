<?php
function nav_item(string $lien, string $titre): string
{
    $classe = 'nav-item';
    if ($_SERVER['SCRIPT_NAME'] === $lien) {
        $classe .= ' active';
    }
    return <<<HTML
    <li class="$classe">
        <a class="nav-link" href="$lien">$titre</a>
    </li>
HTML;
}

function nav_menu(): string
{
    return
        nav_item('/index.php', 'Accueil') .
        nav_item('/contact.php', 'Contact');
}