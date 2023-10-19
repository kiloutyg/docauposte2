<?php

namespace App\Controller;

use Doctrine\ORM\Mapping\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\FrontController;

class ViewsModificationController extends FrontController
{
    #[Route('/viewmod/base', name: 'app_base_views_modification')]
    public function baseViewModificationPageView(): Response
    {

        return $this->render('services/views_modification/base_views_modification.html.twig', [
            'controller_name' => 'ViewsModificationController',
        ]);
    }

    #[Route('/viewmod/modifying', name: 'app_views_modification')]
    public function viewsModification(Request $request)
    {
        $originUrl = $request->headers->get('referer');
        $this->logger->info('Full request: ' . $request);
        foreach ($request->request->keys() as $key) {
            $this->logger->info('Key: ' . $key);
            $structuredKey = $this->viewsModificationService->extractComponentsFromKey($key);
            $this->logger->info('Structured key: ' . json_encode($structuredKey));
            $this->logger->info('Key entity: ' . $structuredKey['entity']);
            $repository = $this->viewsModificationService->defineEntityType($structuredKey['entity']);
            $this->logger->info('repo:' . json_encode($repository));
            if (!$repository) {
                continue;
            }
            $entity = $repository->find($structuredKey['id']);
            $this->logger->info('Entity: ' . json_encode($entity));
            $originalValue = $this->viewsModificationService->defineOriginalValue($entity, $structuredKey['field']);
            if ($originalValue != $request->request->get($key)) {
                $this->logger->info('Original value: ' . $originalValue);
                $this->logger->info('New value: ' . $request->request->get($key));
                $this->viewsModificationService->updateEntity($entity, $structuredKey['field'], $request->request->get($key), $originalValue);
            } else {
                continue;
            }
        }
        return $this->redirect($originUrl);
    }


    #[Route('/view/update', name: 'app_update_view')]
    public function updateView()
    {
        $this->viewsModificationService->updateTheUpdatingOfTheSortOrder();
        return $this->redirectToRoute('app_base');
    }
}