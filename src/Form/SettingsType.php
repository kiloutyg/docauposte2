<?php

namespace App\Form;

use App\Entity\Settings;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateIntervalType;
use Symfony\Component\Form\Extension\Core\Type\FormType;

class SettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            ->add('UploadValidation', CheckboxType::class, [
                'required' => false,
                'label' => 'stuff',
                'attr' => ['class' => 'pretty-toggle'],
            ])
            ->add('ValidatorNumber', ChoiceType::class, [
                'required' => true,
                'choices' => array_combine(range(1, 10), range(1, 10)),
                'label' => false,
                'placeholder' => 'Sélectionner le nombre de validateurs',
            ])

            ->add('AutoDisplayIncident', CheckboxType::class, [
                'required' => false,
                'label' => false,
                'attr' => ['class' => 'pretty-toggle'],
                ])
            ->add('AutoDisplayIncidentTimer', DateIntervalType::class, [
                'required' => true,
                'label' => false,
                'labels' => [
                    'minutes' => false,
                ],                 
                'with_years' => false,
                'with_months' => false,
                'with_days' => false,
                'with_minutes' => true,
                'placeholder' => 'Sélectionner le délai en minutes',
            ])

            ->add('Training', CheckboxType::class, [
                'required' => false,
                'label' => false,
                'attr' => ['class' => 'pretty-toggle'],
            ])
            ->add('OperatorRetrainingDelay', DateIntervalType::class, [
                'required' => true,
                'label' => false,
                'labels' => [
                    'months' => false,
                ], 
                'with_years' => false,
                'with_months' => true,
                'with_days' => false,
                'with_minutes' => false,
                'placeholder' => 'Sélectionner le délai en mois',
            ])
            ->add('AutoDeleteOperatorDelay', DateIntervalType::class, [
                'required' => true,
                'label' => false,
                'labels' => [
                    'months' => false,
                ], 
               'with_years' => false,
                'with_months' => true,
                'with_days' => false,
                'with_minutes' => false,
                'placeholder' => 'Sélectionner le délai en mois',
            ])
        ;

        // Submit Button
        $builder->add('submit', SubmitType::class, [
            'label' => 'Enregistrer',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Settings::class,
        ]);
    }
}
