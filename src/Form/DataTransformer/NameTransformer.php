<?php

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class NameTransformer implements DataTransformerInterface
{
    public function transform($value)
    {
        if (!$value) {
            return '';
        }
        $names = explode('.', $value);
        return strtoupper($names[0]) . ' ' . ucfirst(strtolower($names[1]));
    }

    public function reverseTransform($value)
    {
        $names = explode(' ', $value);
        return strtolower($names[0]) . '.' . strtoupper($names[1]);
    }
}
