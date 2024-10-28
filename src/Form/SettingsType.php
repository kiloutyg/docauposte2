<?php

namespace App\Form;

use App\Entity\Settings;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\DateIntervalType;

class SettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            ->add('UploadValidation', CheckboxType::class, [
                'required' => false,
                'row_attr' => [
                    'class' => ''
                ],
                'label_attr' => [
                    'class' => 'mb-4',
                ],
                'label' => 'Validation des fichiers chargés',
                'attr' => [
                    'class' => 'training-toggle-input',
                ],
            ])

            ->add('ValidatorNumber', ChoiceType::class, [
                'choices' => [
                    '1' => 1,
                    '2' => 2,
                    '3' => 3,
                    '4' => 4,
                    '5' => 5,
                    '6' => 6,
                    '7' => 7,
                    '8' => 8,
                    '9' => 9,
                    '10' => 10,
                ],
                'row_attr' => [
                    'class' => ''
                ],
                'label_attr' => [
                    'class' => 'mb-4',
                ],
                'label' => 'Nombre de validateurs',
            ])

            ->add('Training', CheckboxType::class, [
                'required' => false,
                'row_attr' => [
                    'class' => ''
                ],
                'label_attr' => [
                    'class' => 'mb-4',
                ],
                'label' => 'Signature Operateur',
                'attr' => [
                    'class' => 'training-toggle-input',
                ],
            ])

            ->add('AutoDisplayIncident', CheckboxType::class, [
                'required' => false,
                'row_attr' => [
                    'class' => ''
                ],
                'label_attr' => [
                    'class' => 'mb-4',
                ],
                'label' => 'Affichage automatique des incidents/alertes',
                'attr' => [
                    'class' => 'training-toggle-input',
                ],
            ])

            ->add('AutoDisplayIncidentTimer', DateIntervalType::class, [
                'required' => false,
                'placeholder' => 'Select time interval',
                'attr' => [],
                'row_attr' => [
                    'class' => ''
                ],
                'label_attr' => [
                    'class' => 'mb-4',
                ],
                'label' => 'Delai d\'affichage automatique des incidents/alertes',
                'input' => 'dateinterval',
                'widget' => 'choice',
                'with_years'  => false,
                'with_months' => false,
                'with_days'   => false,
                'with_hours'  => false,
                'with_minutes' => true,
                'with_seconds' => false,
            ])

            ->add('AutoDeleteOperatorDelay', DateIntervalType::class, [
                'required'    => false,
                'placeholder' => 'Select number of months',
                'row_attr' => [
                    'class' => ''
                ],
                'label_attr' => [
                    'class' => 'mb-4',
                ],
                'label' => 'Delai de suppression automatique des opérateurs aprés inactivité',
                'input' => 'dateinterval',
                'widget' => 'choice',
                'with_years'  => false,
                'with_months' => true,
                'with_days'   => false,
                'with_hours'  => false,
                'with_minutes' => false,
                'with_seconds' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Enregistrer',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Settings::class,
        ]);
    }
}
