<?php
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

function nav_menu(string $linkclass = ''): string
{
    return
        nav_item('/index.php', 'Accueil', $linkclass) .
        nav_item('/jeu.php', 'Jeu', $linkclass) .
        nav_item('/contact.php', 'Contact', $linkclass) .
        nav_item('/glace.php', 'Glace', $linkclass);
}

// function checkbox(string $name, array $data): string
// {
//     $checkboxes = [];
//     echo <<<HTML
//      <th> <h5> $name </h5> </th>
//     HTML;

//     foreach ($data as $key => $value) {
//         $attributes = '';

//         $checkboxes[] = <<<HTML

//         <div class="checkbox">
//             <tr>
//             <input type="checkbox" name="{$name}[]" value="{$key}" {$attributes}>
//             <label for="{$name}[]">{$key} - {$value} €</label>
//          </tr>
//         </div>

// HTML;
//         if (isset($data[$name]) && in_array($key, $data[$name])) {
//             $attributes .= 'checked';
//         }
//     }
//     return implode('', $checkboxes);
// }

// checkbox('Parfums', $parfums) 
// checkbox('Cornets', $cornets)
// checkbox('Suppléments', $supplements) 


function checkbox(string $name, string $value, array $data): string
{

    $attributes = '';
    if (isset($data[$name]) && in_array($value, $data[$name]))
        $attributes .= 'checked';
    return <<<HTML
    <input type="checkbox" name="{$name}[]" value="{$value}" {$attributes}> 
HTML;
}
// checkbox('parfums', $parfum, $_GET)
function radio(string $name, string $value, array $data): string
{

    $attributes = '';
    if (isset($data[$name]) && $value === $data[$name])
        $attributes .= 'checked';
    return <<<HTML
    <input type="radio" name="{$name}" value="{$value}" {$attributes}> 
HTML;
}
function print_var_name($variable)
{
    foreach ($GLOBALS as $var_name => $value) {
        if ($value === $variable) {
            return $var_name;
        }
    }

    return false;
}
function dump($variable)
{
    echo '<pre>';
    var_dump($variable);
    echo '</pre>';
}

function creneaux_html(array $creneaux): string
{
    if (empty($creneaux)) {
        return 'Fermé';
    }
    $phrases = [];
    foreach ($creneaux as $creneau) {
        $phrases[] = "de  <strong>{$creneau[0]}h</strong> à  <strong>{$creneau[1]}h</strong>";
    }
    return 'Ouvert ' . implode('   et   ', $phrases);
}
function creneaux2_html(array $creneaux)
{
    echo " Le magasin est ouvert de ";
    foreach ($creneaux as $k => $creneau) {
        if ($k > 0) {
            echo " et de ";
        }
        echo "{$creneau[0]}h à {$creneau[1]}h";
    }
}
/*
Creer une fonction pour retourner un tableau sous la forme: 
    [
        '9h à 12h',
        '14h à 19h'
    ]
    en utilisant implode. 
    Il faut construire le tableau intermédiaire
    qui contiendra le de Xh à Yh,
    puis enfin utiliser implode pour construire la phrase finale.
*/

function creneaux3_html(array $creneaux)
{
    foreach ($creneaux as $creneau) {
        echo "$creneau[0]h à $creneau[1]h" . '<br>';
    }
}