<?php

namespace App\Form;

use App\Entity\Operator;
use App\Entity\Team;
use App\Entity\Uap;

use App\Form\DataTransformer\FirstNameTransformer;
use App\Form\DataTransformer\LastNameTransformer;

use Symfony\Component\Form\Extension\Core\Type\TextType;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SearchType;


use Symfony\Component\OptionsResolver\OptionsResolver;

class OperatorType extends AbstractType
{
    private $firstNameTransformer;
    private $lastNameTransformer;

    public function __construct(FirstNameTransformer $firstNameTransformer, LastNameTransformer $lastNameTransformer)
    {
        $this->firstNameTransformer = $firstNameTransformer;
        $this->lastNameTransformer = $lastNameTransformer;
    }


    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $operatorId = $options['operator_id'] ?? null;
        $builder

            ->add('lastname', TextType::class, [
                'label' => false,

                'attr' => [
                    'class' => 'form-control mx-auto mt-2 capitalize-all-letters',
                    'placeholder' => 'NOM',
                    'required' => true,
                ],
                'row_attr' => [
                    'class' => 'col'
                ],
                'label_attr' => [
                    'class' => 'form-label mb-4',
                    'style' => ' color: #ffffff;'
                ]

            ])
            ->add('firstname', TextType::class, [
                'label' => false,

                'attr' => [
                    'class' => 'form-control mx-auto mt-2',
                    'placeholder' => 'Prenom',
                    'required' => true,
                ],
                'row_attr' => [
                    'class' => 'col'
                ],
                'label_attr' => [
                    'class' => 'form-label mb-4',
                    'style' => ' color: #ffffff;'
                ]

            ])
            ->add('code', TextType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'form-control mx-auto mt-2',
                    'placeholder' => 'Code de l\'opérateur',
                    'required' => true,
                    'maxlength' => '5',
                    'pattern' => '[0-9]{5}',
                ],
                'row_attr' => [
                    'class' => 'col'
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
                'placeholder' => 'Équipe',

                'attr' => [
                    'class' => 'form-control mx-auto mt-2',
                    'required' => true
                ],
                'row_attr' => [
                    'class' => 'col'
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
                'placeholder' => 'UAP',

                'attr' => [
                    'class' => 'form-control mx-auto mt-2',
                    'required' => true
                ],
                'row_attr' => [
                    'class' => 'col'
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
                    'value' => true,
                    'autocomplete' => 'off',
                    'checked' => false,
                ],
                'row_attr' => [
                    'class' => 'col'
                ],
                'label_attr' => [

                    'class' => 'btn btn-outline-primary mb-4',
                    'style' => 'font-weight: bold; color: #ffffff;',
                ],
                'label' => 'Formateur',
            ]);
        $builder->get('firstname')
            ->addModelTransformer($this->firstNameTransformer);

        $builder->get('lastname')
            ->addModelTransformer($this->lastNameTransformer);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Operator::class,
            'operator_id' => null,  // Add a default value for the custom option
        ]);
    }
}
