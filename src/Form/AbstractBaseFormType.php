<?php

namespace App\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


abstract class AbstractBaseFormType extends AbstractType
{
    // CSS class constants
    private const FORM_LABEL_CLASSES = 'form-label fs-6';
    private const FORM_CONTROL_CLASSES = 'form-control mx-auto mb-3';
    private const SUBMIT_BUTTON_CLASSES = 'btn btn-primary btn-login text-uppercase fw-bold mt-2 mb-3 submit-entity-creation';

    // Style constants
    private const LABEL_STYLE = 'color: #ffffff;';

    /**
     * Adds an entity selection field to the form.
     *
     * This method creates a dropdown or selection field for choosing entities from the database.
     * It configures the field with standard styling and allows customization of the query used
     * to fetch entities, as well as how they are displayed.
     *
     * @param FormBuilderInterface $builder          The form builder instance
     * @param string               $fieldName        The name of the field in the form
     * @param string               $label            The label text to display for the field
     * @param string               $entityClass      The fully qualified class name of the entity
     * @param string               $choiceLabel      The property of the entity to use as the displayed text
     * @param callable|null        $queryBuilder     Optional callback to customize the query that fetches entities
     * @param string|null          $placeholder      Optional placeholder text (defaults to label if null)
     * @param bool                 $required         Whether the field is required (default: true)
     * @param array                $additionalOptions Additional options to override or extend default configuration
     *
     * @return void
     */
    protected function addEntityField(
        FormBuilderInterface $builder,
        string $fieldName,
        string $label,
        string $entityClass,
        string $choiceLabel,
        ?callable $queryBuilder = null,
        ?string $placeholder = null,
        ?bool $required = true,
        array $additionalOptions = []
    ): void {
        $options = [
            'label' => $label,
            'class' => $entityClass,
            'choice_label' => $choiceLabel,
            'query_builder' => $queryBuilder,
            'label_attr' => [
                'class' => self::FORM_LABEL_CLASSES,
                'style' => self::LABEL_STYLE
            ],
            'placeholder' => $placeholder ?? $label,
            'attr' => [
                'class' => self::FORM_CONTROL_CLASSES,
                'id' => $fieldName,
            ],
            'required' => $required,

        ];

        // Merge additional options with proper handling of nested arrays
        $this->mergeOptions($options, $additionalOptions);

        $builder->add($fieldName, EntityType::class, $options);
    }



    /**
     * Adds a text field to the form.
     *
     * This method creates a text input field with predefined styling and configuration.
     * It handles label formatting, placeholder text, and allows for additional customization
     * through optional parameters.
     *
     * @param FormBuilderInterface $builder          The form builder instance
     * @param string               $fieldName        The name of the field in the form
     * @param string               $label            The label text to display for the field
     * @param string|null          $placeholder      Optional placeholder text (defaults to label if null)
     * @param bool                 $required         Whether the field is required (default: true)
     * @param array                $additionalOptions Additional options to override or extend default configuration
     *
     * @return void
     */
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
                'class' => self::FORM_LABEL_CLASSES,
                'style' => self::LABEL_STYLE
            ],
            'attr' => [
                'class' => self::FORM_CONTROL_CLASSES,
                'placeholder' => $placeholder ?? $label,
                'id' => $fieldName,
            ],
            'required' => $required,

        ];

        // Merge additional options with proper handling of nested arrays
        $this->mergeOptions($options, $additionalOptions);

        $builder->add($fieldName, TextType::class, $options);
    }

    /**
     * Adds a submit button to the form.
     *
     * This method creates a submit button with predefined styling and allows for customization
     * through additional options.
     *
     * @param FormBuilderInterface $builder          The form builder instance
     * @param string               $label            The button label text, defaults to 'Ajouter'
     * @param array                $additionalOptions Additional options to override or extend default configuration
     *
     * @return void
     */
    protected function addSubmitButton(
        FormBuilderInterface $builder,
        ?string $label = 'Ajouter',
        array $additionalOptions = []
    ): void {
        $options = [
            'label' => $label,
            'attr' => [
                'class' => self::SUBMIT_BUTTON_CLASSES,
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

    /**
     * Adds an enum selection field to the form.
     *
     * This method creates a choice field based on an enum class with predefined styling
     * and configuration. It automatically extracts enum cases and uses their values
     * as both keys and labels.
     *
     * @param FormBuilderInterface $builder          The form builder instance
     * @param string               $fieldName        The name of the field in the form
     * @param string               $label            The label text to display for the field
     * @param string               $enumClass        The fully qualified enum class name
     * @param string|null          $placeholder      Optional placeholder text (defaults to label if null)
     * @param bool                 $required         Whether the field is required (default: true)
     * @param array                $additionalOptions Additional options to override or extend default configuration
     *
     * @return void
     */
    protected function addEnumField(
        FormBuilderInterface $builder,
        string $fieldName,
        string $label,
        string $enumClass,
        ?string $placeholder = null,
        bool $required = true,
        array $additionalOptions = []
    ): void {
        $options = [
            'label' => $label,
            'class' => $enumClass,
            'choice_label' => function ($choice) {
                return $choice->value;
            },
            'label_attr' => [
                'class' => self::FORM_LABEL_CLASSES,
                'style' => self::LABEL_STYLE
            ],
            'placeholder' => $placeholder ?? $label,
            'attr' => [
                'class' => self::FORM_CONTROL_CLASSES,
                'id' => $fieldName,
            ],
            'required' => $required,
        ];

        // Merge additional options with proper handling of nested arrays
        $this->mergeOptions($options, $additionalOptions);

        $builder->add($fieldName, EnumType::class, $options);
    }
}
