<?php

namespace App\Controller;

use App\Repository\ZoneRepository;
use App\Repository\ProductLineRepository;
use App\Repository\CategoryRepository;
use App\Repository\ButtonRepository;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CascadingDropdownController extends BaseController
{


    #[Route('/cascading_dropdowns', name: 'app_cascading_dropdowns')]
    public function index(): Response
    {


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
                'id' => $category->getId(),
                'name' => $category->getName(),
                'product_line_id' => $category->getProductLine()->getId()
            ];
        }, $this->categoryRepository->findAll());

        $buttons = array_map(function ($button) {
            return [
                'id' => $button->getId(),
                'name' => $button->getName(),
                'category_id' => $button->getCategory()->getId()
            ];
        }, $this->buttonRepository->findAll());

        return $this->render('services/uploads/cascading_dropdowns.html.twig', [
            'zones' => $zones,
            'productLines' => $productLines,
            'categories' => $categories,
            'buttons' => $buttons,
        ]);
    }
}