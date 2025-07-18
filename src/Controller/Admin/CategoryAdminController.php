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

    /**
     * Displays the category administration page with category details and related entities.
     *
     * This method renders the admin interface for a specific category, showing its buttons,
     * associated product line, and all categories within that product line. If no category
     * object is provided, it attempts to find the category by ID.
     *
     * @param int|null $categoryId The ID of the category to display. Can be null if category object is provided.
     * @param Category|null $category The category entity object. If null, will be fetched using categoryId.
     *
     * @return Response The rendered admin template with category data and related entities.
     */
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
        $productLineCategories  = $productLine->getCategories();

        return $this->render('admin_template/admin_index.html.twig', [
            'pageLevel'                 => $pageLevel,
            'productLine'               => $productLine,
            'category'                  => $category,
            'categoryButtons'           => $categoryButtons,
            'productLineCategories'     => $productLineCategories,
        ]);
    }



    /**
     * Creates a new button within a specified category.
     *
     * This method handles the creation of a new button entity by validating the button name,
     * checking for duplicates, and persisting the new button to the database. The button name
     * is automatically prefixed with the category name. It also creates the necessary folder
     * structure for the button and provides user feedback through flash messages.
     *
     * @param Request $request The HTTP request object containing the button name in POST data
     * @param int|null $categoryId The ID of the category where the button will be created. Can be null.
     *
     * @return Response A redirect response to the category admin page with appropriate flash messages
     */
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

    /**
     * Deletes a button entity from the system.
     *
     * This method handles the deletion of a button entity by first checking if the current user
     * has the required ROLE_LINE_ADMIN permission. If authorized, it attempts to delete the button
     * and provides appropriate feedback through flash messages. The user is redirected back to
     * the category admin page regardless of the operation outcome.
     *
     * @param int $buttonId The unique identifier of the button to be deleted
     *
     * @return Response A redirect response to the category admin page with success, error, or danger flash messages
     */
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
