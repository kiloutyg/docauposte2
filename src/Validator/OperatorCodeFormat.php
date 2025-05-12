<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class OperatorCodeFormat extends Constraint
{
    public string $message = 'Le code Opérateur doit respecter le format requis.';
}
