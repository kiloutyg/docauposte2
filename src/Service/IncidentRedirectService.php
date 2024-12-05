<?php

namespace App\Service;

use Psr\Log\LoggerInterface;

use Symfony\Component\Routing\RouterInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
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

    private $router;

    private $zoneRepository;
    private $productLineRepository;
    private $categoryRepository;
    private $buttonRepository;

    private $settingsService;

    public function __construct(
        LoggerInterface                 $logger,

        RouterInterface                 $router,

        ZoneRepository                  $zoneRepository,
        ProductLineRepository           $productLineRepository,
        CategoryRepository              $categoryRepository,
        ButtonRepository                $buttonRepository,
        SettingsService                 $settingsService,

    ) {
        $this->logger                   = $logger;

        $this->router                   = $router;

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

        $currentTime = time();
        $this->logger->info('currentTime', [$currentTime]);

        $lastActivity = $session->get('lastActivity');
        $this->logger->info('lastActivity', [$lastActivity]);

        $idleTime = $currentTime - $lastActivity;
        $this->logger->info('idleTime', [$idleTime]);

        $incidentAutoDisplayTimer = ($this->settingsService->getIncidentAutoDisplayTimerInSeconds());
        $this->logger->info('incidentAutoDisplayTimer', [$incidentAutoDisplayTimer]);
        $this->logger->info('idleTime>incidentAutoDisplayTimer', [$idleTime > $incidentAutoDisplayTimer]);

        if ($idleTime > $incidentAutoDisplayTimer && $session->get('inactive')) {
            return $this->redirectionManagement($session);
        }

        return new JsonResponse(['redirect' => false, 'cause' => 'issue in inactivityCheck service not enough idle time(in sec) ' . $idleTime]);
    }




    public function redirectionManagement(SessionInterface $session): JsonResponse
    {

        $routeName = $session->get('stuff_route');
        $this->logger->info('routeName in request', [$routeName]);

        $routeParams =  $session->get('stuff_param');
        $this->logger->info('routeParam in request', [$routeParams]);


        if (($routeName == 'app_zone' || $routeName == 'app_productLine' || $routeName == 'app_category' || $routeName == 'app_button') && $routeParams != null) {
            $response = $this->incidentRouteDetermination($routeName, $routeParams);
            $this->logger->info('respose of incidentRouteDetermination', $response);
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
            $currentIncidentCyclingKey = $session->get('incidentsKeyCycling');

            // Increment the key to move to the next incident
            $currentIncidentCyclingKey++;

            // Check if the key exceeds the number of incidents
            if ($currentIncidentCyclingKey >= $numberOfIncidents) {
                $currentIncidentCyclingKey = 0;
            }

            // Save the updated key back to the session
            $session->set('incidentsKeyCycling', $currentIncidentCyclingKey);

            // Retrieve arrays from the session
            $arrayOfProductLines = $session->get('arrayOfProductLines', []);
            $arrayOfIncidents = $session->get('arrayOfIncidents', []);

            // Ensure the arrays contain the expected data
            if (isset($arrayOfProductLines[$currentIncidentCyclingKey]) && isset($arrayOfIncidents[$currentIncidentCyclingKey])) {
                return new JsonResponse([
                    'redirect' => $this->generateUrl('app_redirected_incident', [
                        'productLineId' => $arrayOfProductLines[$currentIncidentCyclingKey],
                        'incidentId' => $arrayOfIncidents[$currentIncidentCyclingKey]
                    ])
                ]);
            } else {
                return new JsonResponse(['redirect' => false, 'cause' => 'Invalid incident data']);
            }
        }

        return new JsonResponse(['redirect' => false, 'cause' => 'No incidents to display']);
    }





    public function incidentRouteDetermination(string $routeName, array $routeParams)
    {

        // Get the current position
        $this->logger->info('routeName in service', [$routeName]);

        $this->logger->info('routeParameter in service', [$routeParams]);

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

        $incidentsArray = [];

        foreach ($productLines as $productLine) {

            $incidents = $productLine->getIncidents();

            if ($incidents->count() != 0) {
                foreach ($incidents as $incident) {
                    $incidentsArray[] = $incident;
                }
            }
        }

        $this->logger->info('incidentsArray before sorting', [$incidentsArray]);

        if (count($incidentsArray) != 0) {

            $incidentsArraySorted = $this->incidentArraySortByPriority($incidentsArray);

            $incidentsIdsArray = array_map(function ($incident) {
                return $incident->getId();
            }, $incidentsArraySorted);

            $productLinesIdsArray = array_map(function ($incident) {
                return $incident->getProductLine()->getId();
            }, $incidentsArraySorted);

            // return [$productLine->getID(), $incidentsArraySorted[0]->getId()];
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
