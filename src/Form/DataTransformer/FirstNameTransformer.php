<?php

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;


class FirstNameTransformer implements DataTransformerInterface
{
    public function transform($value)
    {
        if (!$value) {
            return '';
        }
        // Transform to ensure first letter is uppercase
        return ucfirst(strtolower($value));
    }

    public function reverseTransform($value)
    {
        // Reverse transform if needed, usually the same as transform for display purposes
        return strtolower($value);
    }
}

class LastNameTransformer implements DataTransformerInterface
{
    public function transform($value)
    {
        if (!$value) {
            return '';
        }
        // Transform to uppercase
        return strtoupper($value);
    }

    public function reverseTransform($value)
    {
        // Store as upper in the database
        return strtoupper($value);
    }
}
