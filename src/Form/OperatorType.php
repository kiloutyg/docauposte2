<?php

namespace App\Form;

use App\Entity\Operator;
use App\Entity\Team;
use App\Entity\Uap;

use App\Form\DataTransformer\FirstNameTransformer;
use App\Form\DataTransformer\LastNameTransformer;

use App\Service\SettingsService;

use Doctrine\ORM\EntityRepository;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

use Symfony\Component\OptionsResolver\OptionsResolver;

class OperatorType extends AbstractType
{
    private $firstNameTransformer;
    private $lastNameTransformer;

    private $settingsService;

    public function __construct(
        FirstNameTransformer $firstNameTransformer,
        LastNameTransformer $lastNameTransformer,
        SettingsService $settingsService,
    ) {
        $this->firstNameTransformer = $firstNameTransformer;
        $this->lastNameTransformer = $lastNameTransformer;
        $this->settingsService = $settingsService;
    }
    private function getCurrentRegexPattern(): string
    {
        return $this->settingsService->getCurrentCodeOpeRegexPattern();
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        /** @var Operator|null $operator */
        $operator = $builder->getData();

        // Default CSS class
        $labelClass = 'btn btn-outline-primary mb-4';

        // Check if the operator exists and has a trainer who is demoted
        if ($operator instanceof Operator && $operator->getTrainer() && $operator->getTrainer()->isDemoted(true)) {
            $labelClass = 'btn btn-outline-danger mb-4';
        }

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
                    'pattern' => $this->getCurrentRegexPattern(),
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
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('t')
                        ->where('t.name != :undefined')
                        ->setParameter('undefined', 'INDEFINI')
                        ->orderBy('t.name', 'ASC');
                },
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
            ->add('uaps', EntityType::class, [
                'class' => Uap::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.name != :undefined')
                        ->setParameter('undefined', 'INDEFINI')
                        ->orderBy('u.name', 'ASC');
                },
                'label' => false,
                'choice_label' => 'name',
                'placeholder' => 'UAP',
                'multiple' => true,
                'expanded' => false,
                'attr' => [
                    'class' => 'form-control mx-auto mt-2',
                    'required' => true,
                    'size' => '2',
                    'data-bs-toggle' => 'tooltip',  // Bootstrap tooltip
                    'title' => 'Maintenez Ctrl pour sélectionner plusieurs UAP',
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
                    'class' => $labelClass,
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
