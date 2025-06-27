<?php

namespace App\Service\Incident;

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






    /**
     * Checks if the user session has been inactive for longer than the configured time limit.
     *
     * This function manages the inactivity tracking by:
     * - Ensuring the session is started
     * - Initializing inactivity tracking if not already set
     * - Comparing the last activity time with the current time
     * - Triggering redirection if the inactivity threshold is exceeded
     *
     * @param Request $request The HTTP request object containing the session
     *
     * @return JsonResponse A JSON response indicating whether redirection is needed:
     *                      - ['redirect' => false, 'cause' => string] if no redirection is needed
     *                      - ['redirect' => URL] if redirection should occur (from redirectionManagement)
     */
    public function inactivityCheck(Request $request): JsonResponse
    {
        $session = $request->getSession();

        if (!$session->isStarted()) {
            $session->start();
        }

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






    /**
     * Manages the redirection process based on the current route stored in the session.
     *
     * This function determines the appropriate incident to display based on the current route
     * and its parameters. It handles the redirection logic for zones, product lines, categories,
     * and buttons, setting up necessary session variables for incident cycling if multiple
     * incidents are found.
     *
     * @param SessionInterface $session The session containing route information and where
     *                                  incident data will be stored
     *
     * @return JsonResponse A JSON response containing either:
     *                      - ['redirect' => URL] with the URL to redirect to if an incident is found
     *                      - ['redirect' => null, 'cause' => string] if no redirection is possible,
     *                        with an explanation of why
     */
    public function redirectionManagement(SessionInterface $session): JsonResponse
    {

        $routeName = $session->get('stuff_route');
        $routeParams =  $session->get('stuff_param');


        if (($routeName == 'app_zone' ||
                $routeName == 'app_productLine' ||
                $routeName == 'app_category' ||
                $routeName == 'app_button') &&
            $routeParams != null
        ) {

            $response = $this->incidentRouteDetermination($routeName, $routeParams);

            if ($response) {
                $session->remove('lastActivity');
                $session->set('inactive', false);

                $arrayOfProductLines = $response[0];
                $session->set('arrayOfProductLines', $arrayOfProductLines);

                $arrayOfIncidents = $response[1];
                $session->set('arrayOfIncidents', $arrayOfIncidents);

                $numberOfIncidents = count($arrayOfIncidents);
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

            $session->remove('lastActivity');
            $session->set('inactive', false);
            return new JsonResponse(['redirect' => null, 'cause' => 'issue in incidentRouteDetermination service response not correct, no incident found']);
        }

        $session->remove('lastActivity');
        $session->set('inactive', false);
        return new JsonResponse(['redirect' => null, 'cause' => 'issue in redirectionManagement service, no correct route stored']);
    }





    /**
     * Handles the cycling between multiple incidents for automatic display.
     *
     * This function checks if it's time to cycle to the next incident based on the
     * cycling timer. If multiple incidents are available and enough time has passed
     * since the last cycle, it will prepare a redirect to the next incident in the sequence.
     *
     * @param Request $request The HTTP request object containing the session with incident data
     *
     * @return JsonResponse A JSON response indicating whether redirection is needed:
     *                      - ['redirect' => URL] if it's time to show the next incident
     *                      - ['redirect' => false, 'cause' => string] if no redirection should occur,
     *                        with an explanation of why
     */
    public function cyclingIncident(Request $request): JsonResponse
    {
        $session = $request->getSession();

        // Get the total number of incidents
        $numberOfIncidents = $session->get('numberOfIncidents');

        if ($numberOfIncidents) {

            if ($this->timeComparison($session->get('cyclingTimer'))) {
                $response = $this->getNextIncidentRedirectResponse($session, $numberOfIncidents);
            } else {
                $response = new JsonResponse(['redirect' => false, 'cause' => 'issue in cyclingIncident service, not enough idle time']);
            }
        }


        if (empty($response)) {
            $response = new JsonResponse(['redirect' => false, 'cause' => 'No incidents to display']);
        }

        return $response;
    }





    /**
     * Prepares a JSON response for redirecting to the next incident in the cycling sequence.
     *
     * This function handles the logic for cycling through multiple incidents by:
     * - Updating the cycling timer to the current time
     * - Incrementing the incident index and handling wrap-around when reaching the end
     * - Determining the correct product line for the current incident
     * - Generating a redirect URL to the next incident if valid data exists
     *
     * @param SessionInterface $session           The session containing incident data and cycling information
     * @param int              $numberOfIncidents The total number of incidents available for cycling
     *
     * @return JsonResponse A JSON response containing either:
     *                      - ['redirect' => URL] with the URL to redirect to the next incident
     *                      - ['redirect' => false, 'cause' => string] if redirection is not possible
     */
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

        $this->logger->info('currentIncidentCyclingKey', [$currentIncidentCyclingKey]);

        // Update session with new index
        $session->set('incidentsKeyCycling', $currentIncidentCyclingKey);

        // Retrieve incident data from session
        $arrayOfProductLines = $session->get('arrayOfProductLines', []);
        $arrayOfIncidents = $session->get('arrayOfIncidents', []);


        if (count($arrayOfProductLines) < $numberOfIncidents && $currentIncidentCyclingKey != 0) {
            $currentProductLineKey = $currentIncidentCyclingKey - 1;
        } else {
            $currentProductLineKey = $currentIncidentCyclingKey;
        }

        // Validate data and generate redirect
        if (
            isset($arrayOfProductLines[$currentProductLineKey]) &&
            isset($arrayOfIncidents[$currentIncidentCyclingKey])
        ) {
            $redirectUrl = $this->generateUrl('app_redirected_incident', [
                'productLineId' => $arrayOfProductLines[$currentProductLineKey],
                'incidentId' => $arrayOfIncidents[$currentIncidentCyclingKey],
            ]);

            return new JsonResponse(['redirect' => $redirectUrl]);
        } else {
            return new JsonResponse(['redirect' => false, 'cause' => 'Invalid incident data']);
        }
    }





    /**
     * Compares a given timestamp with the current time to determine if enough time has elapsed.
     *
     * This function checks if the difference between the current time and the provided timestamp
     * exceeds the configured auto-display timer threshold from the settings service.
     *
     * @param int $time The timestamp to compare against the current time
     *
     * @return bool Returns true if the elapsed time exceeds the configured threshold, false otherwise
     */
    public function timeComparison(int $time): bool
    {

        if ((time() - $time) > $this->settingsService->getIncidentAutoDisplayTimerInSeconds()) {
            return true;
        }
        return false;
    }





    /**
     * Determines the appropriate incidents based on the current route and its parameters.
     *
     * This function identifies the entity (Zone, ProductLine, Category, or Button) from the route
     * and delegates to the appropriate method to retrieve related incidents. It handles the routing
     * logic for finding incidents associated with different levels of the application hierarchy.
     *
     * @param string $routeName   The name of the current route (e.g., 'app_zone', 'app_productLine')
     * @param array  $routeParams The parameters associated with the route, containing entity IDs
     *
     * @return array|false Returns either:
     *                     - An array containing [productLineIdsArray, incidentIdsArray] if incidents are found
     *                     - False if no incidents are found or if the route is not recognized
     */
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





    /**
     * Retrieves incidents associated with a specific zone.
     *
     * This function collects all incidents from product lines belonging to the given zone,
     * sorts them by priority, and returns arrays of product line IDs and incident IDs.
     *
     * @param Zone $zone The zone entity for which to find incidents
     *
     * @return array|false Returns either:
     *                     - An array containing [productLineIdsArray, incidentIdsArray] if incidents are found
     *                     - False if no incidents are found for the zone
     */
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





    /**
     * Retrieves incidents associated with a specific product line.
     *
     * This function collects all incidents from the given product line,
     * sorts them by priority if multiple incidents exist, and returns arrays
     * of the product line ID and incident IDs.
     *
     * @param ProductLine $productLine The product line entity for which to find incidents
     *
     * @return array|false Returns either:
     *                     - An array containing [productLineIdsArray, incidentIdsArray] if incidents are found
     *                     - False if no incidents are found for the product line
     */
    public function incidentByProductLine(ProductLine $productLine)
    {
        $response = false;

        $incidents = $productLine->getIncidents();
        $productLinesIdsArray = array($productLine->getId());

        if ($incidents->count() == 1) {
            $incidentsIdsArray = array($incidents[0]->getId());
            $response = [$productLinesIdsArray, $incidentsIdsArray];
        } elseif ($incidents->count() > 1) {
            foreach ($incidents as $incident) {
                $incidentsArray[] = $incident;
            }
            $incidentsArraySorted = $this->incidentArraySortByPriority($incidentsArray);
            $incidentsIdsArray = array_map(function ($incident) {
                return $incident->getId();
            }, $incidentsArraySorted);
            $response = [$productLinesIdsArray, $incidentsIdsArray];
        }
        return $response;
    }




    /**
     * Retrieves incidents associated with a specific category.
     *
     * This function gets the product line associated with the given category
     * and delegates to incidentByProductLine to retrieve the incidents.
     *
     * @param Category $category The category entity for which to find incidents
     *
     * @return array|false Returns either:
     *                     - An array containing [productLineIdsArray, incidentIdsArray] if incidents are found
     *                     - False if no incidents are found for the category's product line
     */
    public function incidentByCategory(Category $category)
    {
        $productLine = $category->getProductLine();
        return $this->incidentByProductLine($productLine);
    }




    /**
     * Retrieves incidents associated with a specific button.
     *
     * This function gets the category associated with the given button
     * and delegates to incidentByCategory to retrieve the incidents.
     *
     * @param Button $button The button entity for which to find incidents
     *
     * @return array|false Returns either:
     *                     - An array containing [productLineIdsArray, incidentIdsArray] if incidents are found
     *                     - False if no incidents are found for the button's category
     */
    public function incidentByButton(Button $button)
    {
        $category = $button->getCategory();
        return $this->incidentByCategory($category);
    }




    /**
     * Sorts an array of incident objects by their auto-display priority in descending order.
     *
     * This function uses PHP's usort to sort the incidents based on their priority values,
     * ensuring that incidents with higher priority values appear first in the resulting array.
     *
     * @param array $incidentsArray An array of incident objects to be sorted
     *
     * @return array The sorted array of incidents in descending order of priority
     */
    public function incidentArraySortByPriority(array $incidentsArray)
    {
        usort($incidentsArray, function ($a, $b) {
            return $b->getAutoDisplayPriority() <=> $a->getAutoDisplayPriority();
        });

        return $incidentsArray;
    }
}
