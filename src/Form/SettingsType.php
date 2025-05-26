<?php

namespace App\Form;

use App\Entity\Settings;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateIntervalType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class SettingsType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'uploadValidation',
                CheckboxType::class,
                [
                    'required' => false,
                    'label' => 'stuff',
                    'attr' => ['class' => 'pretty-toggle'],
                ]
            )
            ->add(
                'validatorNumber',
                ChoiceType::class,
                [
                    'required' => true,
                    'choices' => array_combine(range(1, 10), range(1, 10)),
                    'label' => false,
                    'placeholder' => 'Sélectionner le nombre de validateurs',
                    'attr' => ['class' => '  align-items-center justify-content-center form-select m-0 w-25'],
                ]
            )
            ->add(
                'incidentAutoDisplay',
                CheckboxType::class,
                [
                    'required' => false,
                    'label' => false,
                    'attr' => ['class' => 'pretty-toggle'],
                ]
            )
            ->add(
                'training',
                CheckboxType::class,
                [
                    'required' => false,
                    'label' => false,
                    'attr' => ['class' => 'pretty-toggle'],
                ]
            )
            ->add(
                'operatorCodeMethod',
                CheckboxType::class,
                [
                    'required' => false,
                    'label' => false,
                    'attr' => ['class' => 'pretty-toggle'],
                ]
            )
            ->add(
                'operatorCodeRegex',
                TextType::class,
                [
                    'required' => false,
                    'label' => false,
                    'attr' => [
                        'class' => 'form-control mx-auto mt-2',
                        'placeholder' => 'Format par défaut: /^[0-9]{5}$/',
                    ]
                ]
            );

        $builder
            ->add(
                'settingsDateInterval',
                FormType::class,
                [
                    'label' => false,
                    'inherit_data' => true,
                    'attr' => ['class' => 'incident-settings-group']
                ]
            );

        $intervalArray =
            [
                'required' => true,
                'label' => false,
                'widget' => 'choice',
                'attr' => ['class' => 'm-0 w-25'],
                'with_days' => false,
                'with_years' => false
            ];
        $months =
            [
                'labels' => [
                    'months' => false,
                ],
                'months' => array_combine(range(1, 12), range(1, 12)),
                'placeholder' => 'Sélectionner le délai en mois',
            ];
        $minutes =
            [
                'labels' => [
                    'minutes' => false,
                ],
                'with_months' => false,
                'with_minutes' => true,
                'minutes' => array_combine(range(1, 60), range(1, 60)),
                'placeholder' => 'Sélectionner le délai en minutes',
            ];
        $builder
            ->get('settingsDateInterval')
            ->add(
                'operatorRetrainingDelay',
                DateIntervalType::class,
                array_merge(
                    $intervalArray,
                    $months
                )
            )
            ->add(
                'operatorInactivityDelay',
                DateIntervalType::class,
                array_merge(
                    $intervalArray,
                    $months
                )
            )
            ->add(
                'operatorAutoDeleteDelay',
                DateIntervalType::class,
                array_merge(
                    $intervalArray,
                    $months
                )
            )
            ->add(
                'incidentAutoDisplayTimer',
                DateIntervalType::class,
                array_merge(
                    $intervalArray,
                    $minutes
                )
            )
        ;

        // Submit Button
        $builder->add(
            'submit',
            SubmitType::class,
            [
                'label' => 'Enregistrer',
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Settings::class,

        ]);
    }
}
