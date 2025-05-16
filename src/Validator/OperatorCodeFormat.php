<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint for validating operator code format.
 *
 * This constraint is used to validate that an operator code follows the required format.
 * It can be applied directly to class properties using PHP 8 attributes.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class OperatorCodeFormat extends Constraint
{
    /**
     * The error message displayed when the operator code format is invalid.
     *
     * @var string
     */
    public string $message = 'Le code Opérateur doit respecter le format requis.';
}
