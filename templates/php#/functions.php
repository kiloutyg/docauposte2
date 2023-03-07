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
        nav_item('/glace.php', 'Glace', $linkclass) .
        nav_item('/menu.php', 'Menu', $linkclass) .
        nav_item('/newsletter.php', 'Newsletter', $linkclass);
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

function creneaux3_html(array $creneaux): string
{
    $phrase = [];
    foreach ($creneaux as $creneau) {
        $phrase[] =  " $creneau[0]h à $creneau[1]h";
    }
    $phrases = 'Le magasin est ouvert de' . implode(' et ', $phrase);
    return $phrases . '.';
}


function creneaux4_html(array $jours, array $creneaux)
{
    foreach ($jours as $k => $jour) {
        if (empty($creneaux[$k])) {
            echo "Le <strong>$jour</strong> le magasin est fermé. <br>";
        } else {
            echo "Le <strong>$jour</strong> ";
            $phrase = [];
            foreach ($creneaux[$k] as $creneau) {
                $phrase[] =  " <strong>$creneau[0]h</strong> à <strong>$creneau[1]h</strong>";
            }
            echo 'le magasin est ouvert de' . implode(' et ', $phrase) . '.' . '<br>';
        }
    }
}

function creneaux5_html(array $jours, array $creneaux)
{
    foreach ($jours as $k => $jour) {
        if ($k + 1 === (int) date('N')) {
            echo '<div class="alert alert-success"> Le magasin est ouvert aujourd\'hui </div>';
            echo '<li style="color:green">';
        } else {
            echo '<li>';
        }
        if (empty($creneaux[$k])) {
            echo "Le <strong>$jour</strong> le magasin est fermé. <br>";
        } else {
            echo "Le <strong>$jour</strong> ";
            $phrase = [];
            foreach ($creneaux[$k] as $creneau) {
                $phrase[] =  " <strong>$creneau[0]h</strong> à <strong>$creneau[1]h</strong>";
            }
            echo 'le magasin est ouvert de' . implode(' et ', $phrase) . '.' . '<br>';
        }
        echo '</li>';
    }
}
// elseif ($k + 1 !== (int) date('N')) {
//             echo '<div class="alert alert-danger"> Le magasin est fermé aujourd\'hui </div>';
//         }

/* Faire une fonction pour avoir le message d'ouverture du magasin en temps reel 
tout au long de la semaine. 
*/

function in_creneaux(int $heure, array $creneaux): bool
{
    foreach ($creneaux as $creneau) {
        $debut = $creneau[0];
        $fin = $creneau[1];
        if ($heure >= $debut && $heure < $fin) {
            return true;
        }
    }
    return false;
}

function creneaux6_html(array $jours, array $creneaux): string
{
    $ouvert = false;
    $phrases = [];
    foreach ($jours as $k => $jour) {
        if (empty($creneaux[$k])) {
            $phrases[] = "Le <strong>$jour</strong> le magasin est fermé.";
        } else {
            $ouvert = true;
            $phrases[] = "Le <strong>$jour</strong> ";
            $phrase = [];
            foreach ($creneaux[$k] as $creneau) {
                $phrase[] =  " <strong>$creneau[0]h</strong> à <strong>$creneau[1]h</strong>";
            }
            $phrases[] = 'le magasin est ouvert de' . implode(' et ', $phrase) . '.';
        }
    }
    if (!$ouvert) {
        return 'Fermé';
    }
    return implode('<br>', $phrases);
}
function creneaux7_html(array $jours, array $creneaux, bool $ouvert)
{

    if ($ouvert === true) {
        echo '<div class="alert alert-success"> Le magasin est actuellement ouvert </div>';
    } else {
        echo '<div class="alert alert-danger"> Le magasin est actuellement fermé </div>';
    }
    foreach ($jours as $k => $jour) {
        if ($k + 1 === (int) date('N')) {
            echo '<li style="color:green">';
        } else {
            echo '<li>';
        }
        if (empty($creneaux[$k])) {
            echo "Le <strong>$jour</strong> le magasin est fermé. <br>";
        } else {
            echo "Le <strong>$jour</strong> ";
            $phrase = [];
            foreach ($creneaux[$k] as $creneau) {
                $phrase[] =  " <strong>$creneau[0]h</strong> à <strong>$creneau[1]h</strong>";
            }
            echo 'le magasin est ouvert de' . implode(' et ', $phrase) . '.' . '<br>';
        }
        echo '</li>';
    }
}
function creneaux8_html(bool $ouvert)
{

    if ($ouvert === true) {
        echo '<div class="alert alert-success"> Le magasin sera ouvert </div>';
    } else {
        echo '<div class="alert alert-danger"> Le magasin sera fermé </div>';
    }
}
//fonction pour afficher un select
function select(string $name, $value, array $options): string
{
    $html_options = [];
    foreach ($options as $k => $option) {
        $attributes = $k == $value ? 'selected' : '';
        $html_options[] = "<option value='$k' $attributes>$option</option>";
    }
    return "<select class='form-control' name='$name'>" . implode($html_options) . '</select>';
}
// function selectheure(string $name, string $value, array $data): string
// {
//     $attributes = '';
//     if (isset($data[$name]))
//         $attributes .= 'selected';
//     return <<<HTML
//     <input  class="form-control" type="number" name="{$name}[]" value="{$value}" {$attributes}> 
// HTML;
// }

//fonction pour afficher un menu au format tsv (tabulation separated values)

// for ($i = 0; $i < 20; $i++) {
//     echo fread($tsvressource, 14);
//     echo '<br>';
//     echo fgets($tsvressource);
//     echo '<br>';
// }
// $line = fgets($tsvressource);
// print_r(explode("\t", $line, 3));
// $lines[] = fgets($tsvressource);
// foreach ($lines as $k => $line) {
//     $lines[$k] = explode("\t", $line, 3);
// }
// var_dump($lines);

// while ($line = fgets($tsvressource)) {
//     $line = explode("\t", $line, 3);

//     if ($line[0] === 'Déssert') {
//         break;
//     } else {
//         echo " <h4> $line[0] </h4>";
//         // echo '<br>';
//         echo $line[1] . '.';
//         echo '<br>';
//         echo $line[2] . '€';
//         echo '<br> <br>';
//     }
// } 
// function menu_tsv(string $name, $ressource, string $endname)
// {
//     while ($line = fgets($ressource)) {

//         $line = explode("\t", $line, 3);
//         if ($line[0] === $name) {
//             echo "<h2> $line[0] </h2>";
//         }
//         if ($line[0] === $endname) {
//             break;
//         } else {
//             echo " <h4> $line[0] </h4>";
//             echo $line[1] . '.';
//             echo '<br>';
//             echo $line[2] . '€';
//             echo '<br> <br>';
//         }
//     }
//     echo '<br> <br> <br> <br>';
//     if (feof($ressource) === true) {
//         fclose($ressource);
//     }
// }

// function menu_tsv(string $name, $ressource, string $endname)
// {

//     while ($line = fgets($ressource)) {
//         $line = explode("\t", $line, 3);

//         if (($line[0] === $endname)) {
//             break;
//         } else {
//             if (empty($line[1] && $line[2]) || $line[0] === $name) {
//                 echo '<br> <br> <br> <br>';
//                 echo " <h1> $line[0] </h1>";
//             } elseif ($line[0] !== $name) {
//                 echo " <h4> $line[0] </h4>";
//                 echo $line[1] . '.';
//                 echo '<br>';
//                 echo $line[2] . '€';
//                 echo '<br> <br>';
//             }
//         }
//     }
// }

// function menu_tsv(string $name, $fichier, string $endname)
// {
//     $ressource = fopen($fichier, 'r');

//     while ($line = fgets($ressource)) {
//         $line = explode("\t", $line, 3);

//         if (($line[0] === $endname)) {
//             break;
//         } else {
//             if (empty($line[1] && $line[2]) || $line[0] === $name) {
//                 echo '<br> <br> <br> <br>';
//                 echo " <h1> $line[0] </h1>";
//             } elseif (($line[0] !== $name)) {
//                 echo " <h4> $line[0] </h4>";
//                 echo $line[1] . '.';
//                 echo '<br>';
//                 echo $line[2] . '€';
//                 echo '<br> <br>';
//             }
//         }
//     }
//     if (feof($ressource) === true) {
//         fclose($ressource);
//     }
// }
// function menu_tsv($fichier)
// {
//     $ressource = fopen($fichier, 'r');

//     while ($line = fgets($ressource)) {
//         $line = explode("\t", $line, 3);


//         if (empty($line[1] && $line[2])) {
//             echo '<br> <br> <br> <br>';
//             echo " <h1> $line[0] </h1>";
//         } elseif ((empty($line[1] && $line[2]) === false)) {
//             echo " <h5> <b> $line[0] </b> </h5>";
//             echo $line[1] . '.';
//             echo '<br>';
//             echo $line[2] . '€';
//             echo '<br> <br>';
//         }
//     }

//     if (feof($ressource) === true) {
//         fclose($ressource);
//     }
// }
// function menu_tsv($fichier)
// {
//     $ressource = fopen($fichier, 'r');
//     while ($line = fgets($ressource)) {
//         $line = explode("\t", trim($line), 3);
//         if (empty($line[1])) {
//             echo '</ul><br> <br>';
//             echo " <h1> $line[0] </h1><ul>";
//         } elseif ((empty($line[1] && $line[2]) === false)) {
//             echo "
//              <h5> <b> $line[0] </b> </h5>
//             <li>$line[1].</li>
//             <li>$line[2]€</li>
//             ";
//         }
//     }
//     if (feof($ressource) === true) {
//         fclose($ressource);
//     }
// }
function menu_tsv($fichier)
{
    $ressource = fopen($fichier, 'r');
    while ($line = fgets($ressource)) {
        $line = explode("\t", trim($line), 3);
        if (empty($line[1])) {
            echo '</ul><br> <br>';
            echo " <h1> $line[0] </h1><ul>";
        } elseif ((empty($line[1] && $line[2]) === false)) {
            $prix = number_format($line[2], 2, ',', '');
            echo <<<HTML
             <br><h5> <b>$line[0]</b> </h5>
             <div class="row">
<div class="col-sm-8">
    <li>$line[1].</li>
</div> 
<div class="col-sm-4">
    <li> <strong> $prix € </strong></li>
</div>
</div>
HTML;
        }
    }
    if (feof($ressource) === true) {
        fclose($ressource);
    }
}