<?php

namespace App\Controller\Support;

use Psr\Log\LoggerInterface;
use App\Service\ViewsModificationService;
use App\Service\EntityFetchingService;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * ViewsModificationController
 *
 * This controller manages the modification of entity views in the application.
 * It provides functionality to display and process changes to various entities
 * through a unified interface, handling the extraction of form data and delegation
 * to appropriate services for updates.
 */
class ViewsModificationController extends AbstractController
{
    /**
     * @var LoggerInterface Logger for recording view modification operations
     */
    private $logger;
    
    /**
     * @var ViewsModificationService Service for handling entity modifications
     */
    private $viewsModificationService;
    
    /**
     * @var EntityFetchingService Service for fetching entities
     */
    private $entityFetchingService;

    /**
     * Constructor for ViewsModificationController
     *
     * Initializes all required services for view modification operations.
     *
     * @param LoggerInterface $logger Logger for recording operations
     * @param ViewsModificationService $viewsModificationService Service for entity modifications
     * @param EntityFetchingService $entityFetchingService Service for fetching entities
     */
    public function __construct(
        LoggerInterface $logger,
        ViewsModificationService $viewsModificationService,
        EntityFetchingService $entityFetchingService,
    ) {
        $this->logger = $logger;
        $this->viewsModificationService = $viewsModificationService;
        $this->entityFetchingService = $entityFetchingService;
    }

    /**
     * Renders the base view modification page
     *
     * This method displays the main interface for entity modifications,
     * providing access to zones and users that can be modified.
     *
     * @return Response The rendered base view modification page
     */
    #[Route('/view/viewmod/base', name: 'app_base_views_modification')]
    public function baseViewModificationPageView(): Response
    {
        return $this->render(
            'services/views_modification/base_views_modification.html.twig',
            [
                'zones' => $this->entityFetchingService->getZones(),
                'users' => $this->entityFetchingService->getUsers()
            ]
        );
    }

    /**
     * Processes entity modifications from form submissions
     *
     * This method handles the processing of submitted form data for entity modifications.
     * It extracts entity information from form field names, validates the entities,
     * and delegates to the ViewsModificationService for actual updates.
     *
     * @param Request $request The HTTP request containing form data
     * @return Response A redirect to the originating page after processing
     */
    #[Route('/view/viewmod/modifying', name: 'app_views_modification')]
    public function viewsModification(Request $request)
    {
        $originUrl = $request->headers->get('referer');
        $entitiesToUpdate = [];

        // Only process fields that were actually submitted
        foreach ($request->request->all() as $key => $newValue) {
            $structuredKey = $this->viewsModificationService->extractComponentsFromKey($key);
            if (!$structuredKey) {
                continue;
            }

            $repository = $this->viewsModificationService->defineEntityType($structuredKey['entity']);
            if (!$repository) {
                continue;
            }

            $entity = $repository->find($structuredKey['id']);
            if (!$entity) {
                continue;
            }

            $originalValue = $this->viewsModificationService->defineOriginalValue($entity, $structuredKey['field']);

            $newValue = $this->defineNewValue($structuredKey, $newValue, $originalValue);
            // Store the entity for update since we know it was modified
            $entitiesToUpdate[] = [
                'entityType' => $structuredKey['entity'],
                'entity' => $entity,
                'field' => $structuredKey['field'],
                'newValue' => $newValue,
                'originalValue' => $originalValue
            ];
        }

        // Process the updates
        foreach ($entitiesToUpdate as $updateInfo) {
            $this->viewsModificationService->updateEntity(
                $updateInfo['entityType'],
                $updateInfo['entity'],
                $updateInfo['field'],
                $updateInfo['newValue'],
                $updateInfo['originalValue']
            );
        }
        return $this->redirect($originUrl);
    }

    /**
     * Processes and formats new values for entity fields
     *
     * This method applies special formatting rules for certain field types,
     * particularly for name fields that may have specific format requirements.
     * It ensures that name fields don't contain disallowed characters and
     * preserves any necessary suffixes from the original value.
     *
     * @param array $structuredKey The structured key containing entity and field information
     * @param string $newValue The new value submitted in the form
     * @param mixed $originalValue The original value of the field
     * @return string The processed new value ready for storage
     */
    private function defineNewValue(array $structuredKey, string $newValue, $originalValue)
    {
        if ($structuredKey['field'] === 'name') {
            // Check if the name does not contain disallowed characters
            if (!preg_match("/^[^.]+$/", $newValue)) {
                $this->logger->error('Invalid name format.');
            }
            $nameParts = explode('.', $originalValue);
            array_shift($nameParts);
            foreach ($nameParts as $namePart) {
                $newValue .= '.' . $namePart;
            }
        }
        return $newValue;
    }
}
