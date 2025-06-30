<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

use App\Entity\ProductLine;
use App\Entity\Category;
use App\Entity\Button;

use App\Service\EntityFetchingService;
use App\Service\AccountService;
use App\Service\SettingsService;
use App\Service\Validation\ValidationService;
use App\Service\Operator\OperatorService;

use Psr\Log\LoggerInterface;

// This controller manage the logic of the front interface, it is the main controller of the application and is responsible for rendering the front interface.
// It is also responsible for creating the super-admin account.

#[Route('/', name: 'app_')]
class FrontController extends AbstractController
{
    private $logger;

    private $authChecker;

    private $accountService;
    private $operatorService;
    private $entityFetchingService;
    private $settingsService;
    private $validationService;



    /**
     * Constructor for the FrontController.
     *
     * Initializes the controller with necessary services and repositories for managing
     * the front interface of the application.
     *
     * @param LoggerInterface $logger                              Logger service for recording operation information and errors
     * @param AuthorizationCheckerInterface $authChecker           Service for checking user authorization
     * @param SettingsService $settingsService                     Service for managing application settings
     * @param OperatorService $operatorService                     Service for operator-related operations
     * @param ValidationService $validationService                 Service for validation-related operations
     * @param EntityFetchingService $entityFetchingService         Service for fetching entities from repositories
     * @param AccountService $accountService                       Service for account management operations
     *
     * @return void
     */
    public function __construct(
        LoggerInterface                     $logger,
        AuthorizationCheckerInterface       $authChecker,

        SettingsService                     $settingsService,
        OperatorService                     $operatorService,
        ValidationService                   $validationService,
        EntityFetchingService               $entityFetchingService,
        AccountService                      $accountService,
    ) {
        $this->logger                       = $logger;
        $this->authChecker                  = $authChecker;

        $this->operatorService              = $operatorService;
        $this->settingsService              = $settingsService;
        $this->validationService            = $validationService;
        $this->entityFetchingService        = $entityFetchingService;
        $this->accountService               = $accountService;
    }



    // This function is responsible for creating the super-admin account at the first connection of the application.
    /**
     * Creates a super-admin account at the first connection of the application.
     *
     * This function checks if any users exist in the system. If no users exist,
     * it attempts to create a super-admin account using the provided request data.
     * If users already exist, it displays an alert message indicating that the
     * super-admin account already exists.
     *
     * @param Request $request The HTTP request containing form data for account creation
     *
     * @return Response A redirect response to the application base route
     */
    #[Route('/createSuperAdmin', name: 'create_super_admin')]
    public function createSuperAdmin(Request $request): Response
    {
        $users = [];
        $users  = $this->entityFetchingService->getUsers();

        if ($users == null) {

            $error = null;
            $result = $this->accountService->createAccount(request: $request);
            if ($result) {
                $this->addFlash('success', 'Le compte de Super-Administrateur a bien été créé.');
            }
            if ($error) {
                $this->addFlash('error', $error);
            }
        } else {
            $this->addFlash('alert', 'Le compte de Super-Administrateur existe déjà.');
            return $this->redirectToRoute('app_base');
        }
        return $this->redirectToRoute('app_base');
    }




    /**
     * Renders the base page of the application.
     *
     * This function serves as the entry point for the application, displaying the main interface.
     * It performs several checks:
     * - Checks for upload validations that need attention
     * - For managers in training mode, checks for inactive operators that need to be deleted
     * - Displays appropriate flash messages for system notifications
     * - Renders the base template with all available zones
     *
     * @return Response A response object containing the rendered base template with zones data
     */
    #[Route('/', name: 'base')]
    public function base(): Response
    {
        $users = $this->entityFetchingService->getUsers();
        $settings = $this->settingsService->getSettings();
        $this->logger->debug(
            'FrontController::base() $settings->isUploadValidation() && $this->entityFetchingService->getValidations() != null',
            [$settings->isUploadValidation(), $this->entityFetchingService->getValidations()]
        );
        if ($settings->isUploadValidation() && $this->entityFetchingService->getValidations() != null) {
            $this->logger->debug('FrontController::base() Reminding to check for upload validations');
            $this->validationService->remindCheck($users);
        }

        if ($settings->isTraining() && $this->authChecker->isGranted('ROLE_MANAGER')) {
            $countArray = $this->operatorService->operatorCheckForAutoDelete();
            if ($countArray != null) {
                // Generate the URL for the operator admin page
                $operatorAdminUrl = $this->generateUrl('app_operator'); // Adjust route name as needed

                // Create the message content first
                $messageContent =
                    ($countArray['findDeactivatedOperators'] === 1
                        ? $countArray['findDeactivatedOperators'] . ' opérateur inactif est à supprimer. '
                        : $countArray['findDeactivatedOperators'] . ' opérateurs inactifs sont à supprimer. ') .
                    ($countArray['toBeDeletedOperators'] === 1
                        ? $countArray['toBeDeletedOperators'] . ' opérateur inactif n\'a été supprimé. '
                        : $countArray['toBeDeletedOperators'] . ' opérateurs inactifs ont été supprimés. ');

                // Then wrap the entire message in an anchor tag
                $this->addFlash(
                    'info',
                    '<a href="' . $operatorAdminUrl . '">' . $messageContent . '</a>'
                );
            }
        }

        return $this->render(
            'base.html.twig',
            [
                'zones'                 => $this->entityFetchingService->getZones(),
            ]
        );
    }




    // Render the zone page
    /**
     * Renders the zone page displaying product lines within a specific zone.
     *
     * This function retrieves a zone by its ID and fetches all product lines associated with it.
     * If there is only one product line in the zone and the user is not a line administrator,
     * the function redirects directly to the product line page. Otherwise, it renders the zone
     * template with the zone and its product lines.
     *
     * @param int|null $zoneId The ID of the zone to display, can be null
     *
     * @return Response A response object containing either the rendered zone template
     *                  or a redirect to the product line page
     */
    #[Route('/zone/{zoneId}', name: 'zone')]
    public function zone(
        ?int $zoneId = null,
    ): Response {

        $zone = $this->entityFetchingService->find(entityType: 'zone',  entityId: $zoneId);

        $productLinesInZone = [];
        $productLinesInZone = $this->entityFetchingService->getProductLinesByZone($zone);

        if (count($productLinesInZone) === 1 && !$this->authChecker->isGranted('ROLE_LINE_ADMIN')) {
            return $this->productLine(null, $productLinesInZone[0]);
        } else {
            return $this->render(
                'zone.html.twig',
                [
                    'zone' => $zone,
                    'productLines' => $productLinesInZone
                ]
            );
        }
    }


    // Render the productLine page and redirect to the mandatory incident page if there is one
    /**
     * Renders the product line page or redirects to appropriate pages based on conditions.
     *
     * This function displays categories within a specific product line. If there are incidents
     * associated with the product line, it redirects to the mandatory incident page. If there
     * is only one category and the user is not a line administrator, it redirects directly to
     * the category page.
     *
     * @param int|null $productLineId The ID of the product line to display, can be null if product line object is provided
     * @param ProductLine|null $productLine The ProductLine object, can be null if productLineId is provided
     *
     * @return Response A response object containing either the rendered product line template
     *                  or a redirect to the category or mandatory incident page
     */
    #[Route('/productLine/{productLineId}', name: 'productLine')]
    public function productLine(?int $productLineId = null, ?ProductLine $productLine = null): Response
    {

        if (!$productLine) {
            $productLine = $this->entityFetchingService->find(entityType: 'productLine', entityId: $productLineId);
        }

        $categoriesInLine = $this->entityFetchingService->getCategoriesByProductLine($productLine);

        $incidentsInProductLine = [];
        $incidentsInProductLine = $this->entityFetchingService->findBy(
            entityType: 'incident',
            criteria: ['productLine' => $productLineId],
            orderBy: ['id' => 'ASC'] // order by id ascending
        );


        if (empty($incidentsInProductLine)) {
            if (count($categoriesInLine) === 1 && !$this->authChecker->isGranted('ROLE_LINE_ADMIN')) {
                return $this->category(null, $categoriesInLine[0]);
            }
            return $this->render(
                'productLine.html.twig',
                [
                    'productLine' => $productLine,
                    'categories' => $categoriesInLine
                ]
            );
        } else {
            $incidentId = $incidentsInProductLine[0]->getId();
            return $this->redirectToRoute('app_mandatory_incident', [
                'productLineId' => $productLine->getid(),
                'incidentId' => $incidentId
            ]);
        }
    }




    // Render the category page and redirect to the button page if there is only one button in the category
    /**
     * Renders the category page displaying buttons within a specific category.
     *
     * This function retrieves a category by its ID or uses a provided Category object,
     * then fetches all buttons associated with it. If there is only one button in the category,
     * the function redirects directly to the button display page. Otherwise, it renders
     * the category template with the category and its buttons.
     *
     * @param int|null $categoryId The ID of the category to display, can be null if category object is provided
     * @param Category|null $category The Category object, can be null if categoryId is provided
     *
     * @return Response A response object containing either the rendered category template
     *                  or a redirect to the button display page
     */
    #[Route('/category/{categoryId}', name: 'category')]
    public function category(?int $categoryId = null, ?Category $category = null): Response
    {

        $buttons = [];
        if (!$category) {
            $category = $this->entityFetchingService->find(entityType: 'category', entityId: $categoryId);
        }

        $buttons = $this->entityFetchingService->getButtonsByCategory($category);

        if (count($buttons) === 1) {
            return $this->buttonDisplay(null, $buttons[0]);
        } else {
            return $this->render(
                'category.html.twig',
                [
                    'category'    => $category,
                    'buttons' => $buttons,
                ]
            );
        }
    }





    // Render the button page and redirect to the upload page if there is only one upload in the button
    /**
     * Renders the button page displaying uploads associated with a specific button.
     *
     * This function retrieves a button by its ID or uses a provided Button object,
     * then fetches all uploads associated with it. If there is only one upload for the button,
     * the function redirects directly to the file download page. Otherwise, it renders
     * the button template with the button and its uploads.
     *
     * @param int|null $buttonId The ID of the button to display, can be null if button object is provided
     * @param Button|null $button The Button object, can be null if buttonId is provided
     *
     * @return Response A response object containing either the rendered button template
     *                  or a redirect to the file download page
     */
    #[Route('/button/{buttonId}', name: 'button')]
    public function buttonDisplay(?int $buttonId = null, ?Button $button = null): Response
    {
        if (!$button) {
            $button = $this->entityFetchingService->find(entityType: 'button', entityId: $buttonId);
        }

        $buttonUploads = $button->getUploads();

        if (count($buttonUploads) != 1) {
            return $this->render(
                'button.html.twig',
                [
                    'button'      => $button,
                    'uploads'     => $buttonUploads,
                ]
            );
        } else {
            $uploadId = $buttonUploads[0]->getId();
            return $this->redirectToRoute('app_download_file', [
                'uploadId' => $uploadId
            ]);
        }
    }
}
