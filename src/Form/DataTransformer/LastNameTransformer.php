<?php

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;


class LastNameTransformer implements DataTransformerInterface
{
    public function transform(mixed $value): mixed
    {
        if (!$value) {
            return null;
        }
        // Transform to uppercase
        return strtoupper($value);
    }

    public function reverseTransform(mixed $value): mixed
    {
        // Store as upper in the database
        return strtoupper($value);
    }
}
