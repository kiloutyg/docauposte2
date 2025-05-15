<?php

namespace App\Validator;

use App\Service\SettingsService;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class OperatorCodeFormatValidator extends ConstraintValidator
{
    private SettingsService $settingsService;

    /**
     * Constructor for the OperatorCodeFormatValidator.
     *
     * @param SettingsService $settingsService Service to retrieve application settings
     */
    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    /**
     * Validates that the operator code matches the required format pattern.
     *
     * This validator checks if the provided operator code value matches
     * the regular expression pattern defined in the application settings.
     *
     * @param mixed      $value      The value that should be validated
     * @param Constraint $constraint The constraint for the validation
     *
     * @throws UnexpectedTypeException If the constraint is not an instance of OperatorCodeFormat
     *
     * @return void
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof OperatorCodeFormat) {
            throw new UnexpectedTypeException($constraint, OperatorCodeFormat::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        $pattern = $this->settingsService->getCurrentCodeOpeRegexPattern();

        if (!preg_match('/^' . $pattern . '$/', $value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
        }
    }
}
