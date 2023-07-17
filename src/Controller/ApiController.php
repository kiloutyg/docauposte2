<?php

namespace App\Controller;

use App\Controller\BaseController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

# This controller is responsible for fetching data from the database and returning it as JSON

class ApiController extends BaseController
{
    #[Route('/api/cascading_dropdown_data', name: 'api_cascading_dropdown_data')]
    public function getData(): JsonResponse
    {
        // Fetch entity categories data to let the cascading dropdown access it

        $zones = array_map(function ($zone) {
            return [
                'id' => $zone->getId(),
                'name' => $zone->getName()
            ];
        }, $this->zoneRepository->findAll());

        $productLines = array_map(function ($productLine) {
            return [
                'id' => $productLine->getId(),
                'name' => $productLine->getName(),
                'zone_id' => $productLine->getZone()->getId()
            ];
        }, $this->productLineRepository->findAll());

        $categories = array_map(function ($category) {
            return [
                'id'                => $category->getId(),
                'name'              => $category->getName(),
                'product_line_id'   => $category->getProductLine()->getId()
            ];
        }, $this->categoryRepository->findAll());

        $buttons = array_map(function ($button) {
            return [
                'id'            => $button->getId(),
                'name'          => $button->getName(),
                'category_id'   => $button->getCategory()->getId()
            ];
        }, $this->buttonRepository->findAll());

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
        }, $this->zoneRepository->findAll());

        $productLines = array_map(function ($productLine) {
            return [
                'id'        => $productLine->getId(),
                'name'      => $productLine->getName(),
                'zone_id'   => $productLine->getZone()->getId()
            ];
        }, $this->productLineRepository->findAll());

        $incidentsCategories = array_map(function ($incidentsCategory) {
            return [
                'id'    => $incidentsCategory->getId(),
                'name'  => $incidentsCategory->getName(),
            ];
        }, $this->incidentCategoryRepository->findAll());


        $responseData = [
            'zones'                 => $zones,
            'productLines'          => $productLines,
            'incidentsCategories'   => $incidentsCategories,
        ];

        return new JsonResponse($responseData);
    }
}