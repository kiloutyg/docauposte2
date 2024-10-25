<?php

namespace App\Form;

use App\Entity\Settings;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
class SettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
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
            ])
            ->add('Training', CheckboxType::class, [
                'required' => false,

                'attr' => [
                    'class' => 'btn-check',
                    'value' => true,
                ],
                'row_attr' => [
                    'class' => 'col'
                ],
                'label_attr' => [

                    'class' => 'btn btn-outline-primary mb-4',
                    'style' => 'font-weight: bold; color: #ffffff;',
                ],
                'label' => 'Signature Operateur',
            ])

            ->add('AutoDisplayIncident', CheckboxType::class, [
                'required' => false,

                'attr' => [
                    'class' => 'btn-check',
                    'value' => true,
                ],
                'row_attr' => [
                    'class' => 'col'
                ],
                'label_attr' => [

                    'class' => 'btn btn-outline-primary mb-4',
                    'style' => 'font-weight: bold; color: #ffffff;',
                ],
                'label' => 'Affichage automatique des incidents/alertes',
            ])

            ->add('AutoDisplayIncidentTimer', TimeType::class, [
                'required' => false,

                'attr' => [],
                'row_attr' => [
                    'class' => 'col'
                ],
                'label_attr' => [
                    'class' => 'mb-4',
                    'style' => '',
                ],
                'label' => 'Delai d\'affichage automatique des incidents/alertes',
                'widget' => 'choice',
                'input' => 'array',
                'input_format' => 'minutes'
            ])
            ->add('AutoDeleteOperatorDelay', ChoiceType::class, [
                'choices'  => [
                    '1 month'  => 1,
                    '2 months' => 2,
                    '3 months' => 3,
                    '4 months' => 4,
                    '5 months' => 5,
                    '6 months' => 6,
                    '7 months' => 7,
                    '8 months' => 8,
                    '9 months' => 9,
                    '10 months' => 10,
                    '11 months' => 11,
                    '12 months' => 12,
                ],
                'placeholder' => 'Select number of months',
                'required'    => false,
                'label' => 'Delai de suppression automatique des opérateurs aprés inactivité',
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
