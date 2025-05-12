<?php

namespace App\Validator;

use App\Service\SettingsService;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class OperatorCodeFormatValidator extends ConstraintValidator
{
    private SettingsService $settingsService;

    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

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
