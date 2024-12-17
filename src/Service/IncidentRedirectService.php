<?php

namespace App\Service;

use Psr\Log\LoggerInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use App\Entity\Zone;
use App\Entity\ProductLine;
use App\Entity\Category;
use App\Entity\Button;

use App\Repository\ZoneRepository;
use App\Repository\ProductLineRepository;
use App\Repository\CategoryRepository;
use App\Repository\ButtonRepository;

use App\Service\SettingsService;

class IncidentRedirectService extends AbstractController
{
    private $logger;

    private $zoneRepository;
    private $productLineRepository;
    private $categoryRepository;
    private $buttonRepository;

    private $settingsService;

    public function __construct(
        LoggerInterface                 $logger,

        ZoneRepository                  $zoneRepository,
        ProductLineRepository           $productLineRepository,
        CategoryRepository              $categoryRepository,
        ButtonRepository                $buttonRepository,
        SettingsService                 $settingsService,

    ) {
        $this->logger                   = $logger;

        $this->settingsService          = $settingsService;

        $this->zoneRepository           = $zoneRepository;
        $this->productLineRepository    = $productLineRepository;
        $this->categoryRepository       = $categoryRepository;
        $this->buttonRepository         = $buttonRepository;
    }




    public function inactivityCheck(Request $request): JsonResponse
    {
        $session = $request->getSession();

        if (!$session->isStarted()) {
            $session->start();
        }

        $this->logger->info('inactive', [$session->get('inactive')]);
        if ($session->get('inactive') === null || $session->get('inactive') === false) {
            $session->set('lastActivity', time());
            $session->set('inactive', true);
            return new JsonResponse(['redirect' => false, 'cause' => 'issue in inactivity check service inactivity timer start']);
        }

        $timeResponse = $this->timeComparison($session->get('lastActivity'));

        if ($timeResponse && $session->get('inactive')) {
            return $this->redirectionManagement($session);
        }

        return new JsonResponse(['redirect' => false, 'cause' => 'issue in inactivityCheck service not enough idle time']);
    }




    public function redirectionManagement(SessionInterface $session): JsonResponse
    {

        $routeName = $session->get('stuff_route');
        $routeParams =  $session->get('stuff_param');


        if (($routeName == 'app_zone' || $routeName == 'app_productLine' || $routeName == 'app_category' || $routeName == 'app_button') && $routeParams != null) {
            $response = $this->incidentRouteDetermination($routeName, $routeParams);
            if ($response) {
                $session->remove('lastActivity', time());
                $session->set('inactive', false);

                $arrayOfProductLines = $response[0];
                $session->set('arrayOfProductLines', $arrayOfProductLines);

                $arrayOfIncidents = $response[1];
                $session->set('arrayOfIncidents', $arrayOfIncidents);

                $numberOfIncidents = count($response[0]);
                if ($numberOfIncidents > 1) {
                    $session->set('numberOfIncidents', $numberOfIncidents);
                    $session->set('incidentsKeyCycling', 0);
                    $session->set('cyclingTimer', time());
                }

                return new JsonResponse([
                    'redirect' => $this->generateUrl('app_redirected_incident', [
                        'productLineId' => $arrayOfProductLines[0],
                        'incidentId' => $arrayOfIncidents[0]
                    ])
                ]);
            }

            $session->remove('lastActivity', time());
            $session->set('inactive', false);
            return new JsonResponse(['redirect' => null, 'cause' => 'issue in incidentRouteDetermination service response not correct, no incident found']);
        }

        $session->remove('lastActivity', time());
        $session->set('inactive', false);
        return new JsonResponse(['redirect' => null, 'cause' => 'issue in redirectionManagement service, no correct route stored']);
    }





    public function cyclingIncident(Request $request): JsonResponse
    {
        $session = $request->getSession();

        // Get the total number of incidents
        $numberOfIncidents = $session->get('numberOfIncidents');

        if ($numberOfIncidents) {
            $timeResponse = $this->timeComparison($session->get('cyclingTimer'));

            if (!$timeResponse) {
                $response = new JsonResponse(['redirect' => false, 'cause' => 'issue in cyclingIncident service, not enough idle time']);
            }

            $response = $this->getNextIncidentRedirectResponse($session, $numberOfIncidents);
        }

        if (empty($response)) {
            $response = new JsonResponse(['redirect' => false, 'cause' => 'No incidents to display']);
        }

        return $response;
    }


    public function getNextIncidentRedirectResponse(SessionInterface $session, int $numberOfIncidents): JsonResponse
    {
        // Update cycling timer
        $session->set('cyclingTimer', time());

        // Retrieve and update incident index
        $currentIncidentCyclingKey = $session->get('incidentsKeyCycling');
        $currentIncidentCyclingKey++;

        // Handle wrap-around
        if ($currentIncidentCyclingKey >= $numberOfIncidents) {
            $currentIncidentCyclingKey = 0;
        }

        // Update session with new index
        $session->set('incidentsKeyCycling', $currentIncidentCyclingKey);

        // Retrieve incident data from session
        $arrayOfProductLines = $session->get('arrayOfProductLines', []);
        $arrayOfIncidents = $session->get('arrayOfIncidents', []);

        // Validate data and generate redirect
        if (
            isset($arrayOfProductLines[$currentIncidentCyclingKey]) &&
            isset($arrayOfIncidents[$currentIncidentCyclingKey])
        ) {
            $redirectUrl = $this->generateUrl('app_redirected_incident', [
                'productLineId' => $arrayOfProductLines[$currentIncidentCyclingKey],
                'incidentId' => $arrayOfIncidents[$currentIncidentCyclingKey],
            ]);
            return new JsonResponse(['redirect' => $redirectUrl]);
        } else {
            return new JsonResponse(['redirect' => false, 'cause' => 'Invalid incident data']);
        }
    }




    public function timeComparison(int $time): bool
    {

        if ((time() - $time) > $this->settingsService->getIncidentAutoDisplayTimerInSeconds()) {
            return true;
        }
        return false;
    }




    public function incidentRouteDetermination(string $routeName, array $routeParams)
    {

        $response = [];

        switch ($routeName) {
            case 'app_zone':
                $zoneId = $routeParams['zoneId'];

                $zone = $this->zoneRepository->find($zoneId);

                $response = $this->incidentByZone($zone);
                break;

            case 'app_productLine':
                $productLineId = $routeParams['productLineId'];

                $productLine = $this->productLineRepository->find($productLineId);

                $response = $this->incidentByProductLine($productLine);

                break;

            case 'app_category':
                $categoryId = $routeParams['categoryId'];

                $category = $this->categoryRepository->find($categoryId);

                $response = $this->incidentByCategory($category);
                break;

            case 'app_button':
                $buttonId = $routeParams['buttonId'];

                $button = $this->buttonRepository->find($buttonId);

                $response = $this->incidentByButton($button);
                break;

            default:

                $response = false;

                break;
        }

        return $response;
    }




    public function incidentByZone(Zone $zone)
    {
        $productLines = $zone->getProductLines();

        $incidentsArray = [];

        foreach ($productLines as $productLine) {

            $incidents = $productLine->getIncidents();

            if ($incidents->count() != 0) {
                foreach ($incidents as $incident) {
                    $incidentsArray[] = $incident;
                }
            }
        }

        if (!empty($incidentsArray)) {

            $incidentsArraySorted = $this->incidentArraySortByPriority($incidentsArray);

            $incidentsIdsArray = array_map(function ($incident) {
                return $incident->getId();
            }, $incidentsArraySorted);

            $productLinesIdsArray = array_map(function ($incident) {
                return $incident->getProductLine()->getId();
            }, $incidentsArraySorted);

            return [$productLinesIdsArray, $incidentsIdsArray];
        }
        return false;
    }




    public function incidentByProductLine(ProductLine $productLine)
    {
        $incidents = $productLine->getIncidents();
        if ($incidents->count() != 0) {
            return [$productLine->getId(), $incidents[0]->getId()];
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

    public function incidentArraySortByPriority(array $incidentsArray)
    {
        usort($incidentsArray, function ($a, $b) {
            return $b->getAutoDisplayPriority() <=> $a->getAutoDisplayPriority();
        });

        return $incidentsArray;
    }
}
