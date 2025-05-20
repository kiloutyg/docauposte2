<?php

namespace App\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

abstract class AbstractBaseFormType extends AbstractType
{
    protected function addEntityField(
        FormBuilderInterface $builder,
        string $fieldName,
        string $label,
        string $entityClass,
        string $choiceLabel,
        callable $queryBuilder,
        ?string $placeholder = null,
        bool $required = true,
        array $additionalOptions = []
    ): void {
        $options = [
            'label' => $label,
            'class' => $entityClass,
            'choice_label' => $choiceLabel,
            'query_builder' => $queryBuilder,
            'label_attr' => [
                'class' => 'form-label fs-4',
                'style' => 'color: #ffffff;'
            ],
            'attr' => [
                'class' => 'form-control',
                'placeholder' => $placeholder ?? $label,
                'id' => $fieldName,
                'required' => $required,
            ]
        ];

        // Merge additional options with proper handling of nested arrays
        $this->mergeOptions($options, $additionalOptions);

        $builder->add($fieldName, EntityType::class, $options);
    }



    protected function addTextField(
        FormBuilderInterface $builder,
        string $fieldName,
        string $label,
        ?string $placeholder = null,
        bool $required = true,
        array $additionalOptions = []
    ): void {
        $options = [
            'label' => $label,
            'label_attr' => [
                'class' => 'form-label fs-4',
                'style' => 'color: #ffffff;'
            ],
            'attr' => [
                'class' => 'form-control',
                'placeholder' => $placeholder ?? $label,
                'id' => $fieldName,
                'required' => $required,
            ]
        ];

        // Merge additional options with proper handling of nested arrays
        $this->mergeOptions($options, $additionalOptions);

        $builder->add($fieldName, TextType::class, $options);
    }

    protected function addSubmitButton(
        FormBuilderInterface $builder,
        string $label = 'Ajouter',
        array $additionalOptions = []
    ): void {
        $options = [
            'label' => $label,
            'attr' => [
                'class' => 'btn btn-primary btn-login text-uppercase fw-bold mt-2 mb-3 submit-entity-creation',
                'type' => 'submit',
            ]
        ];

        // Merge additional options with proper handling of nested arrays
        $this->mergeOptions($options, $additionalOptions);

        $builder->add('save', SubmitType::class, $options);
    }


    /**
     * Merges options with proper handling of nested arrays
     * 
     * @param array $baseOptions The base options array (will be modified)
     * @param array $additionalOptions The additional options to merge
     */
    protected function mergeOptions(array &$baseOptions, array $additionalOptions): void
    {
        foreach ($additionalOptions as $key => $value) {
            // If both are arrays, merge them recursively
            if (isset($baseOptions[$key]) && is_array($baseOptions[$key]) && is_array($value)) {
                $baseOptions[$key] = array_merge($baseOptions[$key], $value);
            } else {
                // Otherwise just override
                $baseOptions[$key] = $value;
            }
        }
    }
}
