<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;


// This class is used to get the name of a variable in twig files 
class GetVariableName extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('getVarName', [$this, 'GetVariableName']),

        ];
    }

    public function GetVariableName($var, $context)
    {
        foreach ($context as $varName => $value) {
            if ($value === $var) {
                return $varName;
            }
        }
        return false;
    }
}