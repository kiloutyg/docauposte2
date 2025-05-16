<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ExclusiveShiftLeadersRelationValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof ExclusiveShiftLeadersRelation) {
            throw new UnexpectedTypeException($constraint, ExclusiveShiftLeadersRelation::class);
        }

        $setFields = 0;
        foreach ($constraint->fields as $field) {
            $getter = 'get' . ucfirst($field);
            if (method_exists($value, $getter) && $value->$getter() !== null) {
                $setFields++;
            }
        }

        if ($setFields !== 1) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ fields }}', implode(', ', $constraint->fields))
                ->addViolation();
        }
    }
}
