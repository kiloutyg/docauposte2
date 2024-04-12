<?php

namespace App\Form;

use App\Entity\Button;
use App\Entity\Operator;
use App\Entity\Team;
use App\Entity\Uap;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\OptionsResolver\OptionsResolver;

class OperatorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $operatorId = $options['operator_id'] ?? null;
        $builder
            ->add('name', TextType::class, [
                'label' => false,

                'attr' => [
                    'class' => 'form-control mx-auto mt-2',
                    'placeholder' => 'Nom de l\'opérateur',
                    'id' => 'name-' . $operatorId,
                    'required' => true,
                ],
                'row_attr' => [
                    'class' => 'col-2'
                ],
                'label_attr' => [
                    'class' => 'form-label mb-4',
                    'style' => 'font-weight:  color: #ffffff;'
                ]

            ])
            ->add('code', TextType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'form-control mx-auto mt-2',
                    'placeholder' => 'Code de l\'opérateur',
                    'id' => 'code-' . $operatorId,
                    'required' => true
                ],
                'row_attr' => [
                    'class' => 'col-2'
                ],
                'label_attr' => [
                    'class' => 'form-label mb-4',
                    'style' => 'font-weight:  color: #ffffff;'
                ]
            ])
            ->add('team', EntityType::class, [
                'class' => Team::class,
                'label' => false,
                'choice_label' => 'name',
                'placeholder' => 'Choisir une équipe',

                'attr' => [
                    'class' => 'form-control mx-auto mt-2',
                    'id' => 'name-' . $operatorId,
                    'required' => true
                ],
                'row_attr' => [
                    'class' => 'col-2'
                ],
                'label_attr' => [
                    'class' => 'form-label mb-4',
                    'style' => 'font-weight:  color: #ffffff;'
                ]
            ])
            ->add('uap', EntityType::class, [
                'class' => Uap::class,
                'label' => false,
                'choice_label' => 'name',
                'placeholder' => 'Choisir une UAP',

                'attr' => [
                    'class' => 'form-control mx-auto mt-2',
                    'id' => 'name-' . $operatorId,
                    'required' => true
                ],
                'row_attr' => [
                    'class' => 'col-2'
                ],
                'label_attr' => [
                    'class' => 'form-label mb-4',
                    'style' => 'font-weight: color: #ffffff;'
                ]
            ])
            ->add('isTrainer', CheckboxType::class, [
                'required' => false,

                'attr' => [
                    'class' => 'btn-check',
                    'id' => 'trainer-' . $operatorId,
                    'value' => true,
                ],
                'row_attr' => [
                    'class' => 'col-2'
                ],
                'label_attr' => [

                    'class' => 'btn btn-outline-primary mb-4',
                    'style' => 'font-weight: bold; color: #ffffff;',
                    'for' => 'trainer-' . $operatorId,
                ],
                'label' => 'Formateur',

            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Operator::class,
            'operator_id' => null,  // Add a default value for the custom option
        ]);
    }
}
