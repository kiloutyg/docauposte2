<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\Button;

use App\Service\Facade\EntityManagerFacade;
use App\Service\Facade\ContentManagerFacade;
use App\Service\ErrorService;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route('/category_admin', name: 'app_category_')]
class CategoryAdminController extends AbstractController
{
    private $entityManagerFacade;
    private $contentManagerFacade;
    private $authChecker;
    private $errorService;

    public function __construct(
        EntityManagerFacade $entityManagerFacade,
        ContentManagerFacade $contentManagerFacade,
        AuthorizationCheckerInterface $authChecker,
        ErrorService $errorService
    ) {
        $this->entityManagerFacade = $entityManagerFacade;
        $this->contentManagerFacade = $contentManagerFacade;
        $this->authChecker = $authChecker;
        $this->errorService = $errorService;
    }

    #[Route('/{categoryId}', name: 'admin')]
    public function categoryAdmin(?int $categoryId = null, ?Category $category = null): Response
    {
        $pageLevel = 'category';
        if ($category === null) {
            $category = $this->entityManagerFacade->find('category', $categoryId);
        }
        if (!$category) {
            $this->errorService->errorRedirectByOrgaEntityType($pageLevel);
        }

        $categoryButtons        = $category->getButtons();
        $productLine            = $category->getProductLine();
        $zone                   = $productLine->getZone();
        $productLineCategories  = $productLine->getCategories();

        $uploads = $this->entityManagerFacade->uploadsByParentEntity(
            'category',
            $category
        );
        $incidents = $this->entityManagerFacade->incidentsByParentEntity(
            'category',
            $category
        );

        $uploadsArray = $this->contentManagerFacade->groupAllUploads($uploads);
        $groupedUploads = $uploadsArray[0];
        $groupedValidatedUploads = $uploadsArray[1];
        $groupIncidents = $this->contentManagerFacade->groupIncidents($incidents);

        return $this->render('admin_template/admin_index.html.twig', [
            'pageLevel'                 => $pageLevel,
            'groupedUploads'            => $groupedUploads,
            'groupedValidatedUploads'   => $groupedValidatedUploads,
            'groupincidents'            => $groupIncidents,
            'zone'                      => $zone,
            'productLine'               => $productLine,
            'category'                  => $category,
            'categoryButtons'           => $categoryButtons,
            'productLineCategories'     => $productLineCategories,
        ]);
    }

    #[Route('/create_button/{categoryId}', name: 'admin_create_button')]
    public function createButton(Request $request, ?int $categoryId = null)
    {
        if (!preg_match("/^[^.]+$/", $request->request->get('buttonname'))) {
            $this->addFlash('danger', 'Nom de bouton invalide');
            return $this->redirectToRoute('app_category_admin', ['categoryId' => $categoryId]);
        } else {
            $category = $this->entityManagerFacade->find('category', $categoryId);
            $buttonname = $request->request->get('buttonname') . '.' . $category->getName();
            $button = $this->entityManagerFacade->findOneBy('button', ['name' => $buttonname]);

            if ($button) {
                $this->addFlash('danger', 'Le bouton existe déjà');
                return $this->redirectToRoute('app_category_admin', ['categoryId' => $categoryId]);
            } else {
                $count = $this->entityManagerFacade->count('button', ['category' => $categoryId]);
                $sortOrder = $count + 1;
                $button = new Button();
                $button->setName($buttonname);
                $button->setCategory($category);
                $button->setSortOrder($sortOrder);
                $button->setCreator($this->getUser());

                $em = $this->entityManagerFacade->getEntityManager();
                $em->persist($button);
                $em->flush();

                $this->contentManagerFacade->folderStructure($buttonname);

                $this->addFlash('success', 'Le bouton a été créé');
                return $this->redirectToRoute('app_category_admin', ['categoryId' => $categoryId]);
            }
        }
    }

    #[Route('/delete_button/{buttonId}', name: 'admin_delete_button')]
    public function deleteEntityButton(int $buttonId): Response
    {
        $entityType = 'button';

        $button = $this->entityManagerFacade->find('button', $buttonId);
        $categoryId = $button->getCategory()->getid();

        if ($this->authChecker->isGranted("ROLE_LINE_ADMIN")) {
            $response = $this->entityManagerFacade->deleteEntity($entityType, $buttonId);
        } else {
            $this->addFlash('error', 'Vous n\'avez pas les droits pour supprimer cette ' . $entityType . '.');
            return $this->redirectToRoute('app_category_admin', ['categoryId' => $categoryId]);
        }

        if ($response) {
            $this->addFlash('success', 'Le bouton ' . $entityType . ' a été supprimé');
            return $this->redirectToRoute('app_category_admin', ['categoryId' => $categoryId]);
        } else {
            $this->addFlash('danger', 'Le bouton ' . $entityType . ' n\'existe pas.');
            return $this->redirectToRoute('app_category_admin', ['categoryId' => $categoryId]);
        }
    }
}
