<?php

namespace App\Form\Iluo;

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

    /**
     * This function returns an array of default options for form elements.
     *
     * @param string $placeholder The placeholder text for the form element. Default is an empty string.
     * @param array|null $optionalAttr An optional array of additional attributes for the form element. Default is null.
     *
     * @return array An array of default options for form elements.
     */
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

    /**
     * Adds an upload field to the form, with optional filtering based on the provided zone and product.
     *
     * @param FormInterface $form The form to which the upload field will be added.
     * @param Zone|null $zone The zone to filter uploads related to. If null, no zone filtering will be applied.
     * @param Products|null $product The product to filter uploads related to. If null, no product filtering will be applied.
     *
     * @return void
     */
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
        $this->logger->debug('WorstationType::buildForm - User department: ', ['department' => $userDepartment]);
        $userZone = $existingData[1];
        $this->logger->debug('WorstationType::buildForm - User zone: ', ['zone' => $userZone]);
        $userUap = $existingData[2];
        $this->logger->debug('WorstationType::buildForm - User UAP: ', ['uap' => $userUap]);

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
            $this->logger->debug('Adding upload field with userZone: ', [$userZone]);
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


    /**
     * Retrieves the user's default department, zone, and UAP.
     *
     * This function retrieves the user's department, and if the user has a department,
     * it attempts to find a default zone and UAP. If the user's department has zones,
     * the function selects the first zone. If the user's department has UAPs,
     * the function selects the first UAP. If the user's department does not have UAPs,
     * the function checks if the user has an operator and selects the operator's UAPs.
     *
     * @param User $user The user for whom to retrieve the default department, zone, and UAP.
     *
     * @return array An array containing the user's default department, zone, and UAP.
     *               The array has the following structure: [userDepartment, userZone, userUap].
     */
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


    /**
     * Handles the pre-submit event for the form.
     *
     * This method retrieves the form data, updates the department and UAP based on the selected zone,
     * and updates the upload field based on the selected zone and product.
     *
     * @param FormEvent $event The form event that triggered this method
     *
     * @return void
     */
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

    /**
     * Updates the department and UAP in the form data based on the selected zone.
     *
     * This function checks if a zone is selected in the form data. If a zone is present,
     * it retrieves the corresponding Zone entity from the database. If the zone has a
     * department associated with it, the department ID is set in the form data. If the
     * department has UAPs, the first UAP ID is set in the form data.
     *
     * @param array &$data The form data array containing zone and department selections
     * @param FormEvent $event The form event that triggered this method
     *
     * @return void
     */
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

    /**
     * Updates the upload field in the form based on the selected zone and product.
     *
     * This method extracts zone and product information from the submitted form data,
     * retrieves the corresponding entities from the database, and updates the upload
     * field with appropriate filtering based on these selections.
     *
     * @param array $data The form data array containing zone and products selections
     * @param FormInterface $form The form instance to which the upload field will be added
     *
     * @return void
     */
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
     * Retrieves a Zone entity by its ID from the database.
     *
     * This method uses the ZoneRepository to find a Zone entity based on the provided zone ID.
     * If a Zone with the given ID exists in the database, it is returned. Otherwise, null is returned.
     *
     * @param int $zoneId The ID of the Zone to retrieve
     *
     * @return Zone|null The Zone entity with the given ID, or null if not found
     */
    private function getZoneById($zoneId)
    {
        return $this->zoneRepository->find($zoneId);
    }



    /**
     * Retrieves a Product entity by its ID from the database.
     *
     * This method uses the ProductsRepository to find a Product entity based on the provided product ID.
     * If a Product with the given ID exists in the database, it is returned. Otherwise, null is returned.
     *
     * @param int $productId The ID of the Product to retrieve
     *
     * @return Products|null The Product entity with the given ID, or null if not found
     */
    private function getProductById($productId)
    {
        return $this->productsRepository->find($productId);
    }



    /**
     * Configures the default options for this form type.
     *
     * @param OptionsResolver $resolver The resolver for the options.
     *
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Workstation::class,
        ]);
    }
}
