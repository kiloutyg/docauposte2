<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class ExclusiveShiftLeadersRelation extends Constraint
{
    public string $message = 'Only one of the fields {{ fields }} should be set.';
    public array $fields = [];

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
