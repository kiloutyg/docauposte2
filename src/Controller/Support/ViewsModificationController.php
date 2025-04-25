<?php

namespace App\Controller\Support;

use App\Service\ViewsModificationService;
use App\Service\EntityFetchingService;

use Psr\Log\LoggerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ViewsModificationController extends AbstractController
{
    private $logger;
    private $viewsModificationService;
    private $entityFetchingService;

    public function __construct(
        LoggerInterface $logger,
        ViewsModificationService $viewsModificationService,
        EntityFetchingService $entityFetchingService,
    ) {
        $this->logger = $logger;
        $this->viewsModificationService = $viewsModificationService;
        $this->entityFetchingService = $entityFetchingService;
    }


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

            if ($structuredKey['field'] === 'name') {
                // Check if the name does not contain disallowed characters
                if (!preg_match("/^[^.]+$/", $newValue)) {
                    $this->logger->error('danger Nom invalide');
                    continue;
                }
                $nameParts = explode('.', $originalValue);
                array_shift($nameParts);
                foreach ($nameParts as $namePart) {
                    $newValue .= '.' . $namePart;
                }
            }
            // Store the entity for update since we know it was modified
            $entitiesToUpdate[] = [
                'entityType' => $structuredKey['entity'],
                'entity' => $entity,
                'field' => $structuredKey['field'],
                'newValue' => $newValue,
                'originalValue' => $originalValue
            ];
        }

        $this->logger->info('entitiesToUpdate', [$entitiesToUpdate]);

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
}
