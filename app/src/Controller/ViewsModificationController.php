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
    #[Route('/view/viewmod/base', name: 'app_base_views_modification')]
    public function baseViewModificationPageView(): Response
    {

        return $this->render('services/views_modification/base_views_modification.html.twig');
    }

    #[Route('/view/viewmod/modifying', name: 'app_views_modification')]
    public function viewsModification(Request $request)
    {
        $originUrl = $request->headers->get('referer');
        $entitiesToUpdate = [];
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
                // Check if the name does not contain the disallowed characters
                if (preg_match("/^[^.]+$/", $request->request->get('$key'))) {
                    // Handle the case when button name contains disallowed characters
                    $this->addFlash('danger', 'Nom invalide');
                }
                $nameParts = explode('.', $originalValue);
                array_shift($nameParts);  // Removing the first key/value from the array
                foreach ($nameParts as $namePart) {
                    $newValue .= '.' . $namePart;
                }
            }
            if ($originalValue != $newValue) {
                // Instead of updating immediately, store the entity and its new value for later processing
                $entitiesToUpdate[] = [
                    'entityType' => $structuredKey['entity'],
                    'entity' => $entity,
                    'field' => $structuredKey['field'],
                    'newValue' => $newValue,
                    'originalValue' => $originalValue
                ];
            }
        }

        // Now process the updates
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
