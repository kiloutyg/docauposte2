<?php

namespace App\Form;

use App\Entity\Workstation;

use App\Entity\Department;
use App\Entity\Products;
use App\Entity\Uap;
use App\Entity\Upload;
use App\Entity\Zone;

use App\Repository\ZoneRepository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Symfony\Component\OptionsResolver\OptionsResolver;

class WorkstationType extends AbstractType
{
    private $zoneRepository;

    public function __construct(
        ZoneRepository $zoneRepository,
    ) {
        $this->zoneRepository   = $zoneRepository;
    }

    private function getDefaultOptions($placeholder = '')
    {
        return [
            'required' => false,
            'placeholder' => $placeholder,
            'attr' => [
                'class' => 'form-control mx-auto mt-2',
            ],
            'label_attr' => [
                'class' => 'form-label',
                'style' => 'font-weight: bold; color: #ffffff;'
            ],
            'row_attr' => [
                'class' => 'col-lg-4 col-md-6 col-sm-12 mb-3'
            ],
        ];
    }

    private function addUploadField($form, ?Zone $zone = null)
    {
        $queryBuilder = function (EntityRepository $er) use ($zone): QueryBuilder {
            $qb = $er->createQueryBuilder('u')
                ->leftJoin('u.validation', 'v')
                ->where('v.id IS NOT NULL')
                ->andWhere('v.status = :validated')
                ->setParameter('validated', 1);

            // If a zone is selected, filter uploads related to that zone
            if ($zone !== null) {
                $qb->leftJoin('u.button', 'b')
                    ->leftJoin('b.category', 'c')
                    ->leftJoin('c.productLine', 'pl')
                    ->leftJoin('pl.zone', 'z')
                    ->andWhere('z.id = :zoneId')
                    ->setParameter('zoneId', $zone->getId());
            }

            return $qb->orderBy('u.path', 'ASC');
        };

        $form->add(
            'upload',
            EntityType::class,
            array_merge(
                [
                    'label' => 'SWI :',
                    'class' => Upload::class,
                    'choice_label' => 'filename',
                    'query_builder' => $queryBuilder,
                    'attr' => [
                        'data-workstation-creation-target' => 'zone',
                        'data-action' => 'change->workstation-creation#zoneChanged'
                    ]
                ],
                $this->getDefaultOptions('Choisir une SWI :')
            )
        );
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du Poste de Travail',
                'label_attr' => [
                    'class' => 'form-label fs-4',
                    'style' => 'color: #ffffff;'
                ],
                'attr' => [
                    'class' => 'form-control capitalize-all-letters',
                    'placeholder' => 'Nom du Poste de Travail',
                    'id' => 'name',
                    'required' => false,
                    'data-name-validation-target' => 'workstationName',
                    'data-action' => 'keyup->name-validation#validateWorkstationName',
                ],
                'row_attr' => [
                    'class' => 'col-lg-4 col-md-6 col-sm-12 mb-3'
                ]
            ])
            ->add(
                'products',
                EntityType::class,
                array_merge(
                    [
                        'label' => 'Produit :',
                        'class' => Products::class,
                        'choice_label' => 'name'
                    ],
                    $this->getDefaultOptions('Choisir un Produit :')
                )
            )
            ->add(
                'department',
                EntityType::class,
                array_merge(
                    [
                        'label' => 'Service :',
                        'class' => Department::class,
                        'choice_label' => 'name',
                    ],
                    $this->getDefaultOptions('Choisir un Service :')
                )
            )
            ->add(
                'zone',
                EntityType::class,
                array_merge(
                    [
                        'label' => 'Zone :',
                        'class' => Zone::class,
                        'choice_label' => 'name',
                        'attr' => [
                            'data-workstation-creation-target' => 'zone',
                            'data-action' => 'change->workstation-creation#zoneChanged'
                        ]
                    ],
                    $this->getDefaultOptions('Choisir une Zone :')
                )
            )
            ->add(
                'uap',
                EntityType::class,
                array_merge(
                    [
                        'label' => 'UAP :',
                        'class' => Uap::class,
                        'choice_label' => 'name',
                    ],
                    $this->getDefaultOptions('Choisir une UAP :')
                )
            );

        // Add the upload field initially without zone filtering
        $this->addUploadField($builder);

        // Add event listener to update the upload field when zone changes
        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) {
                $data = $event->getData();
                $form = $event->getForm();

                // Check if zone is selected
                if (isset($data['zone']) && !empty($data['zone'])) {
                    // Get the Zone entity
                    $zone = $this->getZoneById($data['zone']);

                    // Update the upload field with zone filtering
                    $this->addUploadField($form, $zone);
                }
            }
        );

        $builder->add('save', SubmitType::class, [
            'label' => 'Ajouter',
            'attr' => [
                'class' => 'btn btn-primary btn-login text-uppercase fw-bold mt-2 mb-3 submit-entity-creation',
                'type' => 'submit',
                'data-name-validation-target' => 'saveButton',
            ]
        ]);
    }

    /**
     * Helper method to get Zone entity by ID
     */
    private function getZoneById($zoneId)
    {
        return $this->zoneRepository->find($zoneId);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Workstation::class,
        ]);
    }
}
