<?php

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;


class FirstNameTransformer implements DataTransformerInterface
{
    public function transform(mixed $value): mixed
    {
        if (!$value) {
            return null;
        }
        // Transform to ensure first letter is uppercase
        return ucfirst(strtolower($value));
    }

    public function reverseTransform(mixed $value): mixed
    {
        if (!$value) {
            return '';
        }
        // Reverse transform if needed, usually the same as transform for display purposes
        return strtolower($value);
    }
}
