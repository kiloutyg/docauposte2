<?php

namespace App\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use App\Entity\Category;
use App\Entity\ProductLine;

use App\Service\Facade\EntityManagerFacade;
use App\Service\Facade\ContentManagerFacade;
use App\Service\ErrorService;

#[Route('/productline_admin', name: 'app_productLine_')]
// This controller manage the logic of the productLine admin interface
class ProductLineAdminController extends AbstractController
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
     * Displays the product line administration interface.
     *
     * This method renders the main administration page for a specific product line,
     * providing access to manage categories and other product line-related entities.
     * It retrieves the product line entity either from the provided parameter or by
     * fetching it from the database using the product line ID. If the product line
     * is not found, it redirects to an error page.
     *
     * @param int|null $productLineId The unique identifier of the product line to display.
     *                                If null, the productLine parameter must be provided.
     * @param ProductLine|null $productLine The product line entity object. If provided,
     *                                      it takes precedence over productLineId parameter.
     *
     * @return Response A rendered response containing the product line admin interface
     *                  or an error redirect if the product line is not found.
     */
    #[Route('/{productLineId}', name: 'admin')]
    public function productLineAdmin(?int $productLineId = null, ?ProductLine $productLine = null): Response
    {
        $pageLevel = 'productLine';

        if ($productLine === null) {
            $productLine = $this->entityManagerFacade->find('productLine', $productLineId);
        }
        if (!$productLine) {
            return $this->errorService->errorRedirectByOrgaEntityType($pageLevel);
        }
        $zone = $productLine->getZone();
        return $this->render('admin_template/admin_index.html.twig', [
            'pageLevel'                 => $pageLevel,
            'zone'                      => $zone,
            'productLine'               => $productLine,
            'zoneProductLines'          => $zone->getProductLines()
        ]);
    }



    // This function will create a new category
    /**
     * Creates a new category within a specified product line.
     *
     * This method handles the creation of a new category entity by validating the category name,
     * checking for duplicates, and persisting the new category to the database. The category name
     * is automatically prefixed with the product line name and validated to ensure it doesn't
     * contain dots. If successful, it also creates the corresponding folder structure and redirects
     * back to the product line admin interface with appropriate flash messages.
     *
     * @param Request $request The HTTP request object containing the category name in the request data
     * @param int|null $productLineId The unique identifier of the product line where the category will be created
     *
     * @return Response A redirect response to the product line admin page with success/error flash messages
     */
    #[Route('/create_category/{productLineId}', name: 'admin_create_category')]
    public function createCategory(Request $request, ?int $productLineId = null)
    {
        $productLine = $this->entityManagerFacade->find('productLine', $productLineId);

        if (!preg_match("/^[^.]+$/", $request->request->get('categoryname'))) {
            // Handle the case when category name contains disallowed characters
            $this->addFlash('danger', 'Nom de catégorie invalide');
            return $this->redirectToRoute('app_productLine_admin', ['productLineId' => $productLineId]);
        } else {

            // Check if the category already exists by looking for a category with the same name
            $categoryname = $request->request->get('categoryname') . '.' . $productLine->getName();
            $category = $this->entityManagerFacade->findOneBy('category', ['name' => $categoryname]);

            // If the category already exists, redirect to the productLine admin interface  with a flash message
            if ($category) {
                $this->addFlash('danger', 'La catégorie existe deja');
                return $this->redirectToRoute('app_productLine_admin', ['productLineId' => $productLineId]);
                // If the category doesn't exist, create it and redirect to the productLine admin interface with a flash message
            } else {
                $count = $this->entityManagerFacade->count('category', ['productLine' => $productLineId]);
                $sortOrder = $count + 1;
                $category = new Category();
                $category->setName($categoryname);
                $category->setProductLine($productLine);
                $category->setSortOrder($sortOrder);
                $category->setCreator($this->getUser());

                $em = $this->entityManagerFacade->getEntityManager();
                $em->persist($category);
                $em->flush();
                $this->contentManagerFacade->folderStructure($categoryname);
                $this->addFlash('success', 'La catégorie a été créée');
                return $this->redirectToRoute('app_productLine_admin', ['productLineId' => $productLineId]);
            }
        }
    }




    /**
     * Deletes a category and all of its children entities from the system.
     *
     * This method handles the deletion of a category entity, including proper authorization
     * checks and cascade deletion of related child entities. Only users with ROLE_LINE_ADMIN
     * privileges are authorized to perform this operation. After deletion, the user is
     * redirected back to the product line admin interface with appropriate flash messages.
     *
     * @param int $categoryId The unique identifier of the category to be deleted
     *
     * @return Response A redirect response to the product line admin page with success/error flash messages
     */
    #[Route('/delete_category/{categoryId}', name: 'admin_delete_category')]
    public function deleteEntityCategory(int $categoryId): Response
    {
        $entityType = 'category';

        $category = $this->entityManagerFacade->find('category', $categoryId);
        // Check if the user is the creator of the entity or if he is a super admin
        if ($this->authChecker->isGranted("ROLE_LINE_ADMIN")) {

            $response = $this->entityManagerFacade->deleteEntity($entityType, $categoryId);
        } else {
            $productLineId = $category->getProductLine()->getId();
            $this->addFlash('error', 'Vous n\'avez pas les droits pour supprimer cette ' . $entityType . '.');
            return $this->redirectToRoute('app_productLine_admin', ['productLineId' => $productLineId]);
        }

        $productLineId = $category->getProductLine()->getId();
        if ($response) {
            $this->addFlash('success', 'La catégorie ' . $category->getName() . ' a été supprimée.');
            return $this->redirectToRoute('app_productLine_admin', ['productLineId' => $productLineId]);
        } else {
            $this->addFlash('danger', 'La catégorie ' . $category->getName() . ' n\'existe pas.');
            return $this->redirectToRoute('app_productLine_admin', ['productLineId' => $productLineId]);
        }
    }
}
