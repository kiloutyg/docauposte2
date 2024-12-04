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
            $this->logger->info('respose of incidentRouteDetermination', [$response]);
            if ($response) {
                $session->invalidate();
                return new JsonResponse([
                    'redirect' => $this->generateUrl('app_mandatory_incident', [
                        'productLineId' => $response[0],
                        'incidentId' => $response[1]
                    ])
                ]);
            }
            $session->set('lastActivity', time());

            return new JsonResponse(['redirect' => false, 'cause' => 'issue in incidentRouteDetermination service response not correct']);
        }

        $session->set('lastActivity', time());

        return new JsonResponse(['redirect' => false, 'cause' => 'issue in redirectionManagement service, no correct route stored']);
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
        $incidentsArraySorted = $this->incidentArraySortByPriority($incidentsArray);

        if (count($incidentsArraySorted) != 0) {

            $productLine = $incidentsArraySorted[0]->getProductLine();

            return [$productLine->getID(), $incidentsArraySorted[0]->getId()];
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
