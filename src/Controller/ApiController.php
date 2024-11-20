<?php

namespace App\Controller;


use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

use App\Service\EntityFetchingService;
use App\Service\SettingsService;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

# This controller is responsible for fetching data from the database and returning it as JSON

class ApiController extends AbstractController
{
    private $entityFetchingService;
    private $settingsService;

    public function __construct(
        EntityFetchingService $entityFetchingService,
        SettingsService $settingsService,
    ) {
        $this->entityFetchingService = $entityFetchingService;
        $this->settingsService = $settingsService;
    }


    #[Route('/api/entity_data', name: 'api_entity_data')]
    public function getData(): JsonResponse
    {
        // Fetch entity categories data to let the cascading dropdown access it

        $zones = array_map(function ($zone) {
            return [
                'id'    => $zone->getId(),
                'name'  => $zone->getName(),
                'sortOrder' => $zone->getSortOrder()
            ];
        }, $this->entityFetchingService->getZones());

        $productLines = array_map(function ($productLine) {
            return [
                'id'        => $productLine->getId(),
                'name'      => $productLine->getName(),
                'zone_id'   => $productLine->getZone()->getId(),
                'sortOrder' => $productLine->getSortOrder()
            ];
        }, $this->entityFetchingService->getProductLines());

        $categories = array_map(function ($category) {
            return [
                'id'                => $category->getId(),
                'name'              => $category->getName(),
                'product_line_id'   => $category->getProductLine()->getId(),
                'sortOrder'         => $category->getSortOrder()
            ];
        }, $this->entityFetchingService->getCategories());

        $buttons = array_map(function ($button) {
            return [
                'id'            => $button->getId(),
                'name'          => $button->getName(),
                'category_id'   => $button->getCategory()->getId(),
                'sortOrder'     => $button->getSortOrder()
            ];
        }, $this->entityFetchingService->getButtons());

        $incidentsCategories = array_map(function ($incidentsCategory) {
            return [
                'id'    => $incidentsCategory->getId(),
                'name'  => $incidentsCategory->getName(),
            ];
        }, $this->entityFetchingService->getIncidentCategories());

        $departments = array_map(function ($department) {
            return [
                'id'    => $department->getId(),
                'name'  => $department->getName(),
            ];
        }, $this->entityFetchingService->getDepartments());


        $responseData = [
            'zones'                 => $zones,
            'productLines'          => $productLines,
            'categories'            => $categories,
            'buttons'               => $buttons,
            'incidentsCategories'   => $incidentsCategories,
            'departments'           => $departments,

        ];

        return new JsonResponse($responseData);
    }


    #[Route('/api/user_data', name: 'api_user_data')]
    public function getUserData(): JsonResponse
    {
        $filteredUsers = [];
        $allUsers = $this->entityFetchingService->getUsers();
        $currentUser = $this->getUser();

        foreach ($allUsers as $user) {
            if ((in_array('ROLE_LINE_ADMIN_VALIDATOR', $user->getRoles())) || (in_array('ROLE_ADMIN_VALIDATOR', $user->getRoles()))) {
                if ($user !== $currentUser) {
                    $filteredUsers[] = [
                        'id'        => $user->getId(),
                        'username'  => $user->getUsername(),
                    ];
                }
            }
        }

        $responseData = [
            'users'   => $filteredUsers,
        ];

        return new JsonResponse($responseData);
    }




    #[Route('/api/settings', name: 'api_settings_data')]
    public function getSettings(): JsonResponse
    {
        $settings = $this->settingsService->getSettings();

        $uploadValidation = $settings->isUploadValidation();
        $validatorNumber = $settings->getValidatorNumber();
        $training = $settings->isTraining();
        $operatorRetrainingDelay = $settings->getOperatorRetrainingDelay();
        $OperatorAutoDeleteDelay = $settings->getOperatorAutoDeleteDelay();

        $responseData = [
            'uploadValidation' => $uploadValidation,
            'validatorNumber' => $validatorNumber,
            'training' => $training,
            'operatorRetrainingDelay' => $operatorRetrainingDelay,
            'OperatorAutoDeleteDelay' => $OperatorAutoDeleteDelay
        ];

        return new JsonResponse($responseData);
    }
}
