<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class GetVariableName extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('getVarName', [$this, 'GetVariableName']),

        ];
    }

    // Not in use yet
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