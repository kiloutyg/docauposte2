<?php

namespace App\Form;

use App\Entity\Button;
use App\Entity\Operator;
use App\Entity\Team;
use App\Entity\Uap;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\OptionsResolver\OptionsResolver;

class OperatorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'form-control mx-auto mt-2',
                    'placeholder' => 'Nom de l\'opérateur',
                    'id' => 'name',
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
            ->add('code', TextType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'form-control mx-auto mt-2',
                    'placeholder' => 'Code de l\'opérateur',
                    'id' => 'code',
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
            ->add('Team', EntityType::class, [
                'class' => Team::class,
                'label' => false,
                'choice_label' => 'name',
                'placeholder' => 'Choisir une équipe',

                'attr' => [
                    'class' => 'form-control mx-auto mt-2',
                    'id' => 'name',
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
                    'id' => 'name',
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
            // ->add('addOperator', SubmitType::class, [
            //     'label' => 'AJouter l\'Operateur',
            //     'attr' => [
            //         'class' => 'btn btn-primary btn-login text-uppercase fw-mt-2 mb-2 submit-operator-creation',
            //         'type' => 'submit'
            //     ]
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Operator::class,
        ]);
    }
}
