<?php

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;


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
