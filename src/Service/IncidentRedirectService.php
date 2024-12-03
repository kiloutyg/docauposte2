<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Psr\Log\LoggerInterface;

use App\Entity\Zone;
use App\Entity\ProductLine;
use App\Entity\Category;
use App\Entity\Button;
use App\Repository\ZoneRepository;
use App\Repository\ProductLineRepository;
use App\Repository\CategoryRepository;
use App\Repository\ButtonRepository;




class IncidentRedirectService extends AbstractController
{
    private $logger;

    private $zoneRepository;
    private $productLineRepository;
    private $categoryRepository;
    private $buttonRepository;


    public function __construct(
        LoggerInterface                 $logger,
        ZoneRepository                  $zoneRepository,
        ProductLineRepository           $productLineRepository,
        CategoryRepository              $categoryRepository,
        ButtonRepository                $buttonRepository,

    ) {
        $this->logger                   = $logger;


        $this->zoneRepository           = $zoneRepository;
        $this->productLineRepository    = $productLineRepository;
        $this->categoryRepository       = $categoryRepository;
        $this->buttonRepository         = $buttonRepository;
    }

    public function incidentRouteDetermination(Request $request)
    {

        // Get the current position
        $routeName = $request->attributes->get('_route');
        // $this->logger->info('routeName in service', [$routeName]);

        // $routeParameter = $request->attributes->get('_route_params');
        // $this->logger->info('routeParameter in service', [$routeParameter]);

        $response = [];

        switch ($routeName) {
            case 'app_zone':

                $zoneId = $request->attributes->get('zoneId');
                // $this->logger->info('zoneId', [$zoneId]);

                $zone = $this->zoneRepository->find($zoneId);
                // $this->logger->info('zone', [$zone]);

                $response = $this->incidentByZone($zone);

                break;

            case 'app_productLine':

                $productLineId = $request->attributes->get('productLineId');
                // $this->logger->info('productLineId', [$productLineId]);

                $productLine = $this->productLineRepository->find($productLineId);
                // $this->logger->info('productLine', [$productLine]);

                $response = $this->incidentByProductLine($productLine);

                break;

            case 'app_category':

                $categoryId = $request->attributes->get('categoryId');
                // $this->logger->info('categoryId', [$categoryId]);

                $category = $this->categoryRepository->find($categoryId);
                // $this->logger->info('category', [$category]);

                $response = $this->incidentByCategory($category);

                break;

            case 'app_button':

                $buttonId = $request->attributes->get('buttonId');
                // $this->logger->info('buttonId', [$buttonId]);

                $button = $this->buttonRepository->find($buttonId);
                // $this->logger->info('button', [$button]);

                $response = $this->incidentByButton($button);
                break;
        }


        if ($response) {
            $this->logger->info('Response: ', $response);
            return $response;
        }

        $this->logger->info(
            'Response because if($reponse) failed: ',
            [
                $response
            ]
        );
        return false;
    }


    public function incidentByZone(Zone $zone)
    {
        $productLines = $zone->getProductLines();
        // $this->logger->info('productLines', [$productLines]);
        // $this->logger->info('productLines count', [$productLines->count()]);

        $incidentsArray = [];

        foreach ($productLines as $productLine) {

            // $this->logger->info('ProductLineName', [$productLine->getName()]);

            $incidents = $productLine->getIncidents();
            // $this->logger->info('incidents', [$incidents]);
            // $this->logger->info('incidents count', [$incidents->count()]);

            if ($incidents->count() != 0) {
                foreach ($incidents as $incident) {
                    $incidentsArray[] = $incident;
                }
            }
        }

        // $this->logger->info('incidentsPersistentCollection', [$incidentsArray]);
        // $this->logger->info('incidentsPersistentCollection count', [count($incidentsArray)]);

        if (count($incidentsArray) != 0) {
            // $this->logger->info('$incidentsArray', [$incidentsArray]);

            $productLine = $incidentsArray[0]->getProductLine();
            // $this->logger->info('productLine ', [$productLine]);

            $productLineId = $productLine->getID();
            // $this->logger->info('productLineId', [$productLineId]);

            $incidentsId = $incidentsArray[0]->getId();
            // $this->logger->info('incidentsId', [$incidentsId]);

            return [$productLineId, $incidentsId];
            // return
            //     [
            //         'productLineId' => $productLineId,
            //         'incidentId' => $incidentsId
            //     ];
        }
        return false;
    }


    public function incidentByProductLine(ProductLine $productLine)
    {
        $incidents = $productLine->getIncidents();
        if ($incidents->count() != 0) {
            return [$productLine->getId(), $incidents[0]];
        }
        return false;
    }


    public function incidentByCategory(Category $category)
    {
        $productLine = $category->getProductLine();
        return $this->incidentByProductLine($productLine);
    }


    public function incidentByButton(Button $button)
    {
        $category = $button->getCategory();
        return $this->incidentByCategory($category);
    }
}