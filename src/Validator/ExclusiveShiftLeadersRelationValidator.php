<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ExclusiveShiftLeadersRelationValidator extends ConstraintValidator
{
    /**
     * Validates that exactly one of the specified fields in the constraint is set.
     *
     * This validator ensures that among all the fields specified in the constraint,
     * exactly one field has a non-null value. If zero or more than one field is set,
     * a validation violation is added.
     *
     * @param mixed $value The value to validate, typically an object with getter methods
     * @param Constraint $constraint The constraint to validate against, must be an instance of ExclusiveShiftLeadersRelation
     *
     * @throws UnexpectedTypeException If the constraint is not an instance of ExclusiveShiftLeadersRelation
     *
     * @return void
     */
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
