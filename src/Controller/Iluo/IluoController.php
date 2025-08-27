<?php

namespace App\Controller\Iluo;

use \Psr\Log\LoggerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

use App\Entity\Operator;

use App\Service\Iluo\IluoService;


#[Route('/iluo/', name: 'app_iluo_')]
class IluoController extends AbstractController
{
    private $logger;
    private $authChecker;
    private $iluoService;
    /**
     * Constructor for the IluoController class.
     *
     * Initializes the controller with necessary services for logging, authorization,
     * entity fetching, and ILUO-specific operations.
     *
     * @param LoggerInterface $logger An instance of LoggerInterface for logging purposes.
     * @param AuthorizationCheckerInterface $authChecker An instance of AuthorizationCheckerInterface for checking user permissions.
     * @param IluoService $iluoService A service specific to ILUO operations.
     */
    public function __construct(
        LoggerInterface                     $logger,
        AuthorizationCheckerInterface       $authChecker,

        IluoService                         $iluoService
    ) {
        $this->logger                       = $logger;
        $this->authChecker                  = $authChecker;

        $this->iluoService                  = $iluoService;
    }



    /**
     * Handles the GET request for the base admin page.
     *
     * This function checks if the incoming request is a GET method and if the user has the ROLE_LINE_ADMIN role.
     * If both conditions are met, it renders the admin template. Otherwise, it adds a warning flash message
     * and redirects to the base application route.
     *
     * @param Request $request The incoming HTTP request object
     *
     * @return Response Returns either a rendered template response for authorized GET requests
     *                  or a redirect response with a warning flash message for unauthorized or non-GET requests
     */
    #[Route('admin', name: 'admin')]
    public function baseAdminPageGet(Request $request): Response
    {
        if ($request->isMethod('GET') && $this->authChecker->isGranted('ROLE_LINE_ADMIN')) {
            return $this->render('/services/iluo/iluo_admin.html.twig');
        }
        $this->addFlash('warning', 'Accés non authorisé');
        return $this->redirectToRoute('app_base');
    }




    /**
     * Updates the ILUO checklist for a specific operator.
     *
     * This function triggers the `checkIluoUpdates` method from the IluoService,
     * which is responsible for checking and potentially applying updates to the ILUO checklist.
     * If an operator ID or entity is provided, the function will update the checklist for the specified operator.
     * Otherwise, it will update the checklist for all operators. After execution, it sets a success flash message
     * indicating the number of updates and redirects the user to the ILUO admin page.
     *
     * @param int|null $operatorId The ID of the operator for whom the checklist should be updated.
     * @param Operator|null $operatorEntity The Operator entity for whom the checklist should be updated.
     *
     * @return Response A RedirectResponse to the ILUO admin page.
     */
    public function iluoChecklistUpdatebySpecificOperator(?int $operatorId = null, ?Operator $operatorEntity = null): Response
    {
        $count = $this->iluoService->checkIluoUpdates();

        $this->addFlash('success', " $count ILUO checklist created successfully.");

        return $this->redirectToRoute('app_iluo_admin');
    }





    /**
     * Tests the ILUO checklist update functionality.
     *
     * This route handler triggers the `checkIluoUpdates` method from the IluoService,
     * which is responsible for checking and potentially applying updates to the ILUO checklist.
     * After execution, it sets a success flash message indicating the number of updates
     * and redirects the user to the ILUO admin page.
     *
     * @return Response A RedirectResponse to the ILUO admin page.
     */
    #[Route('test_checklist', name: 'test_checklist')]
    public function testIluoChecklist(): Response
    {
        $count = $this->iluoService->checkIluoUpdates();

        $this->addFlash('success', " $count Test ILUO checklist executed successfully.");

        return $this->redirectToRoute('app_iluo_admin');
    }



    /**
     * Deletes all ILUO checklists.
     *
     * This route handler triggers the `deleteAllIluos` method from the IluoService
     * to remove all existing ILUO checklist entries from the database. After the deletion,
     * it sets a success flash message indicating the number of deleted checklists
     * and redirects the user to the ILUO admin page.
     *
     * @return Response A RedirectResponse to the ILUO admin page.
     */
    #[Route('delete_checklist', name: 'delete_checklist')]
    public function deleteIluoChecklist(): Response
    {
        if ($this->authChecker->isGranted('ROLE_SUPER_ADMIN')) {
            $count = $this->iluoService->deleteAllIluos();
            $this->addFlash(type: 'success', message: "$count ILUO checklist deleted successfully.");
        } else {
            $this->addFlash('warning', 'Vous n\'êtes pas autorisé à supprimer les checklists ILUO.');
        }

        return $this->redirectToRoute('app_iluo_admin');
    }




    #[Route(path: 'views', name: 'views')]
    public function iluoViews(): Response
    {
        return $this->render('/services/iluo/iluo_views.html.twig');
    }

    #[Route(path: 'views_search', name: 'views_search')]
    public function iluoViewsSearch(): Response
    {
        return $this->render('/services/iluo/iluo_views_component/iluo_views_search_component.html.twig');
    }


    #[Route(path: 'views_checklist', name: 'views_checklist')]
    public function iluoViewsChecklist(): Response
    {
        return $this->render('/services/iluo/iluo_views_component/iluo_views_checklist_component.html.twig');
    }



    #[Route(path: 'views_matrices', name: 'views_matrices')]
    public function iluoViewsMatrices(): Response
    {
        return $this->render('/services/iluo/iluo_views_component/iluo_views_matrices_component.html.twig');
    }
}
