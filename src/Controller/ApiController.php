<?php

namespace App\Controller;

use App\Controller\BaseController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

# This controller is responsible for fetching data from the database and returning it as JSON

class ApiController extends FrontController
{
    #[Route('/api/cascading_dropdown_data', name: 'api_cascading_dropdown_data')]
    public function getData(): JsonResponse
    {
        // Fetch entity categories data to let the cascading dropdown access it

        $zones = array_map(function ($zone) {
            return [
                'id'    => $zone->getId(),
                'name'  => $zone->getName(),
                'sortOrder' => $zone->getSortOrder()
            ];
        }, $this->cacheService->zones->toArray());

        $productLines = array_map(function ($productLine) {
            return [
                'id'        => $productLine->getId(),
                'name'      => $productLine->getName(),
                'zone_id'   => $productLine->getZone()->getId(),
                'sortOrder' => $productLine->getSortOrder()
            ];
        }, $this->cacheService->productLines->toArray());

        $categories = array_map(function ($category) {
            return [
                'id'                => $category->getId(),
                'name'              => $category->getName(),
                'product_line_id'   => $category->getProductLine()->getId(),
                'sortOrder'         => $category->getSortOrder()
            ];
        }, $this->cacheService->categories->toArray());

        $buttons = array_map(function ($button) {
            return [
                'id'            => $button->getId(),
                'name'          => $button->getName(),
                'category_id'   => $button->getCategory()->getId(),
                'sortOrder'     => $button->getSortOrder()
            ];
        }, $this->cacheService->buttons->toArray());

        $responseData = [
            'zones'         => $zones,
            'productLines'  => $productLines,
            'categories'    => $categories,
            'buttons'       => $buttons,
        ];

        return new JsonResponse($responseData);
    }

    #[Route('/api/incidents_cascading_dropdown_data', name: 'api_incidents_cascading_dropdown_data')]
    public function getIncidentData(): JsonResponse
    {
        // Fetch entity categories data to let the cascading dropdown access it

        $zones = array_map(function ($zone) {
            return [
                'id'        => $zone->getId(),
                'name'      => $zone->getName()
            ];
        }, $this->cacheService->zones->toArray());

        $productLines = array_map(function ($productLine) {
            return [
                'id'        => $productLine->getId(),
                'name'      => $productLine->getName(),
                'zone_id'   => $productLine->getZone()->getId()
            ];
        }, $this->cacheService->productLines->toArray());

        $incidentsCategories = array_map(function ($incidentsCategory) {
            return [
                'id'    => $incidentsCategory->getId(),
                'name'  => $incidentsCategory->getName(),
            ];
        }, $this->cacheService->incidentCategories->toArray());


        $responseData = [
            'zones'                 => $zones,
            'productLines'          => $productLines,
            'incidentsCategories'   => $incidentsCategories,
        ];

        return new JsonResponse($responseData);
    }

    #[Route('/api/department_data', name: 'api_department_data')]
    public function getDepartmentData(): JsonResponse
    {
        $departments = array_map(function ($department) {
            return [
                'id'    => $department->getId(),
                'name'  => $department->getName(),
            ];
        }, $this->cacheService->departments->toArray());

        $responseData = [

            'departments'   => $departments,
        ];

        return new JsonResponse($responseData);
    }

    #[Route('/api/user_data', name: 'api_user_data')]
    public function getUserData(): JsonResponse
    {
        $filteredUsers = [];
        $allUsers = $this->cacheService->users;
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

    #[Route('/api/sortOrder', name: 'api_sortOrder_data')]
    public function getSortOrder(): JsonResponse
    {
        // Fetch entity categories data to let the cascading dropdown access it

        $zones = array_map(function ($zone) {
            return [
                'id'    => $zone->getId(),
                'name'  => $zone->getName(),
                'sortOrder' => $zone->getSortOrder()
            ];
        }, $this->cacheService->zones->toArray());

        $productLines = array_map(function ($productLine) {
            return [
                'id'        => $productLine->getId(),
                'name'      => $productLine->getName(),
                'zone_id'   => $productLine->getZone()->getId(),
                'sortOrder' => $productLine->getSortOrder()
            ];
        }, $this->cacheService->productLines->toArray());

        $categories = array_map(function ($category) {
            return [
                'id'                => $category->getId(),
                'name'              => $category->getName(),
                'product_line_id'   => $category->getProductLine()->getId(),
                'sortOrder'         => $category->getSortOrder()
            ];
        }, $this->cacheService->categories->toArray());

        $buttons = array_map(function ($button) {
            return [
                'id'            => $button->getId(),
                'name'          => $button->getName(),
                'category_id'   => $button->getCategory()->getId(),
                'sortOrder'     => $button->getSortOrder()
            ];
        }, $this->cacheService->buttons->toArray());

        $responseData = [
            'zones'         => $zones,
            'productLines'  => $productLines,
            'categories'    => $categories,
            'buttons'       => $buttons,
        ];

        return new JsonResponse($responseData);
    }



    #[Route('/api/settings', name: 'api_settings_data')]
    public function getSettings(): JsonResponse
    {
        $uploadValidation = $this->cacheService->settings->isUploadValidation();
        $validatorNumber = $this->cacheService->settings->getValidatorNumber();
        $training = $this->cacheService->settings->isTraining();
        $operatorRetrainingDelay = $this->cacheService->settings->getOperatorRetrainingDelay();
        $OperatorAutoDeleteDelay = $this->cacheService->settings->getOperatorAutoDeleteDelay();

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
