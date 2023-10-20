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

        foreach ($request->request->keys() as $key) {

            $structuredKey = $this->viewsModificationService->extractComponentsFromKey($key);

            $repository = $this->viewsModificationService->defineEntityType($structuredKey['entity']);

            if (!$repository) {
                continue;
            }
            $entity = $repository->find($structuredKey['id']);

            $originalValue = $this->viewsModificationService->defineOriginalValue($entity, $structuredKey['field']);

            $newValue = $request->request->get($key);

            if ($structuredKey['field'] == 'name') {
                $nameParts = explode('.', $originalValue);
                array_shift($nameParts);  // Removing the first key/value from the array
                foreach ($nameParts as $namePart) {
                    $newValue .= '.' . $namePart;
                }
            }
            if ($originalValue != $newValue) {
                $this->viewsModificationService->updateEntity($structuredKey['entity'], $entity, $structuredKey['field'], $newValue, $originalValue);
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