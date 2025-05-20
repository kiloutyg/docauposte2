<?php

namespace App\Controller\Iluo;

use \Psr\Log\LoggerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

use App\Entity\Products;
use App\Entity\ShiftLeaders;
use App\Entity\QualityRep;
use App\Entity\Workstation;

use App\Form\ProductType;
use App\Form\ShiftLeadersType;
use App\Form\QualityRepType;
use App\Form\WorkstationType;

use App\Service\EntityFetchingService;
use App\Service\IluoService;


#[Route('/iluo/', name: 'app_iluo_')]
class IluoController extends AbstractController
{
    private $logger;
    private $authChecker;
    private $entityFetchingService;
    private $iluoService;
    /**
     * Constructor for the IluoController class.
     *
     * Initializes the controller with necessary services for logging, authorization,
     * entity fetching, and ILUO-specific operations.
     *
     * @param LoggerInterface $logger An instance of LoggerInterface for logging purposes.
     * @param AuthorizationCheckerInterface $authChecker An instance of AuthorizationCheckerInterface for checking user permissions.
     * @param EntityFetchingService $entityFetchingService A service for fetching various entities.
     * @param IluoService $iluoService A service specific to ILUO operations.
     */
    public function __construct(
        LoggerInterface                     $logger,
        AuthorizationCheckerInterface       $authChecker,

        EntityFetchingService               $entityFetchingService,
        IluoService                         $iluoService
    ) {
        $this->logger                       = $logger;
        $this->authChecker                  = $authChecker;

        $this->entityFetchingService        = $entityFetchingService;
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
}
