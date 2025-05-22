<?php

namespace App\Form;

use App\Entity\Workstation;

use App\Entity\Department;
use App\Entity\Products;
use App\Entity\Uap;
use App\Entity\Upload;
use App\Entity\User;
use App\Entity\Zone;

use App\Repository\ProductsRepository;
use App\Repository\ZoneRepository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr\Join;

use Psr\Log\LoggerInterface;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Symfony\Bundle\SecurityBundle\Security;

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
    private $logger;
    private $security;

    private $productsRepository;
    private $zoneRepository;

    public function __construct(
        LoggerInterface         $logger,
        Security                $security,

        ProductsRepository      $productsRepository,
        ZoneRepository          $zoneRepository
    ) {
        $this->logger               = $logger;
        $this->security             = $security;

        $this->productsRepository   = $productsRepository;
        $this->zoneRepository       = $zoneRepository;
    }

    private function getDefaultOptions(string $placeholder = '', ?array $optionalAttr = []): array
    {
        return [
            'required' => true,
            'placeholder' => $placeholder,
            'attr' => array_merge(
                [
                    'class' => 'form-control mx-auto mt-2',
                ],
                $optionalAttr
            ),

            'label_attr' => [
                'class' => 'form-label',
                'style' => 'font-weight: bold; color: #ffffff;'
            ],
            'row_attr' => [
                'class' => 'col-lg-4 col-md-6 col-sm-12 mb-3'
            ],
        ];
    }

    public function addUploadField($form, ?Zone $zone = null, ?Products $product = null): void
    {

        $this->logger->debug('Adding upload field ', ['zone' => $zone, 'product' => $product]);

        $queryBuilder = function (EntityRepository $er) use ($zone, $product): QueryBuilder {
            $qb = $er->createQueryBuilder('u')
                ->select('u')
                ->leftJoin('App\Entity\Validation', 'v', Join::WITH, 'v.Upload = u')
                ->where('v.id IS NOT NULL')
                ->andWhere('v.status = :isValidated')
                ->setParameter('isValidated', true);

            // If a zone is selected, filter uploads related to that zone
            if ($zone !== null) {
                $this->logger->debug('Adding zone filter ', ['zone' => $zone]);
                $qb->leftJoin('App\Entity\Button', 'b', Join::WITH, 'b.id = u.button')
                    ->leftJoin('App\Entity\Category', 'c', Join::WITH, 'c.id = b.category')
                    ->leftJoin('App\Entity\ProductLine', 'pl', Join::WITH, 'pl.id = c.productLine')
                    ->leftJoin('App\Entity\Zone', 'z', Join::WITH, 'z.id = pl.zone')
                    ->andWhere('z.id = :zoneId')
                    ->setParameter('zoneId', $zone->getId());
            }

            if ($product !== null) {
                $this->logger->debug('Adding product filter ', ['product' => $product]);
                // Extract just the product name without spaces for better matching
                $productNameSimplified = str_replace(' ', '', $product->getName());
                // Match paths that contain the product name (case insensitive)
                $qb->andWhere('LOWER(u.path) LIKE LOWER(:productPattern)')
                    ->setParameter('productPattern', '%' . $productNameSimplified . '%');
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
                    'query_builder' => $queryBuilder
                ],
                $this->getDefaultOptions('Choisir une SWI :', [
                    'data-workstation-form-target' => 'upload',
                    'data-action' => 'change->workstation-form#fieldsChanged'
                ])
            )
        );
    }


    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        if ($user = $this->security->getUser()) {
            // If he exist get the current user's information
            $existingData = $this->preExistingUserData($user);
        }

        // Initialize default values
        $userDepartment = $existingData[0];
        $userZone = $existingData[1];
        $userUap = $existingData[2];

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
                'zone',
                EntityType::class,
                array_merge(
                    [
                        'label' => 'Zone :',
                        'class' => Zone::class,
                        'choice_label' => 'name',
                        'data' => $userZone,
                    ],
                    $this->getDefaultOptions('Choisir une Zone :', [
                        'data-workstation-form-target' => 'zone',
                        'data-action' => 'change->workstation-form#fieldsChanged'
                    ])
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
                        'data' => $userDepartment,
                    ],
                    $this->getDefaultOptions('Choisir un Service :', [
                        'data-workstation-form-target' => 'department',
                        'data-action' => 'change->workstation-form#fieldsChanged'
                    ])
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
                        'data' => $userUap,
                        'query_builder' => function (EntityRepository $er) {
                            return $er->createQueryBuilder('u')
                                ->where('u.name != :undefined')
                                ->setParameter('undefined', 'INDEFINI')
                                ->orderBy('u.name', 'ASC');
                        },
                    ],
                    $this->getDefaultOptions('Choisir une UAP :', [
                        'data-workstation-form-target' => 'uap',
                        'data-action' => 'change->workstation-form#fieldsChanged'
                    ])
                )
            )
            ->add(
                'products',
                EntityType::class,
                array_merge(
                    [
                        'label' => 'Produit :',
                        'class' => Products::class,
                        'choice_label' => 'name'
                    ],
                    $this->getDefaultOptions('Choisir un Produit :', [
                        'data-workstation-form-target' => 'product',
                        'data-action' => 'change->workstation-form#fieldsChanged'
                    ])
                )
            );

        if ($userZone) {
            $this->logger->debug('Adding upload field with userZone: ' . $userZone);
            $this->addUploadField($builder, $userZone);
        } else {
            $this->logger->debug('Adding upload field without userZone');
            // Add the upload field initially without zone filtering
            $this->addUploadField($builder);
        }

        // Add event listener to update the upload field when zone changes
        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            [$this, 'uploadFilterDetermination']
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


    public function preExistingUserData(User $user)
    {
        // Initialize default values
        $userDepartment = null;
        $userZone = null;
        $userUap = null;

        // Get user's department
        $userDepartment = $user->getDepartment();

        // If user has a department, try to get a default zone and UAP
        if ($userDepartment) {
            // Get the first zone from the department's zones collection (if any)
            $zones = $userDepartment->getZones();
            if ($zones && !$zones->isEmpty()) {
                $userZone = $zones->first();
            }

            // Get the first UAP from the department's UAPs collection (if any)
            $uaps = $userDepartment->getUaps();
            if ($uaps && !$uaps->isEmpty()) {
                $userUap = $uaps->first();
            } else {
                $userOperator = $user->getOperator();
                if ($userOperator) {
                    $userUap = $userOperator->getUaps();
                }
            }
        }
        return [$userDepartment, $userZone, $userUap];
    }


    public function uploadFilterDetermination(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        $this->logger->info('Pre-submit event triggered');

        // Update department and UAP if zone is selected
        $this->updateDepartmentAndUapFromZone($data, $event);

        // Update upload field based on selected zone and product
        $this->updateUploadField($data, $form);
    }

    public function updateDepartmentAndUapFromZone(array &$data, FormEvent $event): void
    {
        if (isset($data['zone']) && !empty($data['zone'])) {
            $zone = $this->getZoneById($data['zone']);

            if ($zone && $zone->getDepartment()) {
                $data['department'] = $zone->getDepartment()->getId();

                if ($zone->getDepartment()->getUaps() && !$zone->getDepartment()->getUaps()->isEmpty()) {
                    $data['uap'] = $zone->getDepartment()->getUaps()->first()->getId();
                }
            }

            $event->setData($data);
        }
    }

    public function updateUploadField(array $data, FormInterface $form): void
    {
        $zone = null;
        $product = null;

        if (isset($data['zone']) && !empty($data['zone'])) {
            $zone = $this->getZoneById($data['zone']);
        }

        if (isset($data['products']) && !empty($data['products'])) {
            $product = $this->getProductById($data['products']);
        }

        $this->logger->debug('Updating upload field', [
            'zone' => $zone ? $zone->getId() : null,
            'product' => $product ? $product->getId() : null
        ]);

        $this->addUploadField($form, $zone, $product);
    }

    /**
     * Helper method to get Zone entity by ID
     */
    private function getZoneById($zoneId)
    {
        return $this->zoneRepository->find($zoneId);
    }

    /**
     * Helper method to get Product entity by ID
     */
    private function getProductById($productId)
    {
        return $this->productsRepository->find($productId);
    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Workstation::class,
        ]);
    }
}
