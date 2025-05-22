<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class ExclusiveShiftLeadersRelation extends Constraint
{
    public string $message = 'Only one of the fields {{ fields }} should be set.';
    public array $fields = [];


    /**
     * Constructor for the ExclusiveShiftLeadersRelation constraint.
     *
     * Initializes the constraint with the specified fields, message, options, groups, and payload.
     * This constraint ensures that only one of the specified fields is set.
     *
     * @param array       $fields   The fields to check for exclusivity
     * @param string|null $message  Custom error message to display when validation fails
     * @param array|null  $options  Additional options for the constraint
     * @param array|null  $groups   The validation groups this constraint belongs to
     * @param mixed       $payload  Additional payload for the constraint
     */
    public function __construct(
        array $fields = [],
        ?string $message = null,
        ?array $options = null,
        ?array $groups = null,
        $payload = null
    ) {
        parent::__construct($options ?? [], $groups, $payload);

        $this->fields = $fields;
        if ($message !== null) {
            $this->message = $message;
        }
    }
    /**
     * Defines the targets this constraint can be applied to.
     *
     * This method specifies that this constraint should be applied at the class level
     * rather than to individual properties.
     *
     * @return string The target type (CLASS_CONSTRAINT)
     */
    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
