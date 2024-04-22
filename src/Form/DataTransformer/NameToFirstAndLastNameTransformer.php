<?php

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class NameToFirstAndLastNameTransformer implements DataTransformerInterface
{

    public function transform($value)
    {
        if (!$value) {
            return ['firstName' => '', 'lastName' => ''];
        }
        $names = explode('.', $value->getName());
        return [
            'firstName' => ucfirst(strtolower($names[1])),
            'lastName' => strtoupper($names[0])
        ];
    }

    public function reverseTransform($values)
    {
        return strtolower($values['lastName']) . '.' . strtoupper($values['firstName']);
    }
}
