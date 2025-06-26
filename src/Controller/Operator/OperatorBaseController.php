<?php

namespace App\Controller\Operator;

use App\Entity\OldUpload;

use App\Repository\UploadRepository;
use App\Repository\ValidationRepository;

use App\Service\EntityFetchingService;
use App\Service\Operator\OperatorService;

use App\Service\Factory\RepositoryFactory;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use \Psr\Log\LoggerInterface;

use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class OperatorBaseController extends AbstractController
{

    private $logger;

    // Repository methods
    private $validationRepository;
    private $uploadRepository;

    // Services methods

    private $entityFetchingService;
    private $operatorService;
    private $repositoryFactory;

    private $docAndOpeTemplatePath;


    public function __construct(

        LoggerInterface                 $logger,

        // Repository classes
        ValidationRepository            $validationRepository,
        UploadRepository                $uploadRepository,

        // Services classes
        EntityFetchingService           $entityFetchingService,
        OperatorService                 $operatorService,
        RepositoryFactory $repositoryFactory

    ) {
        $this->logger                       = $logger;

        // Variables related to the repositories
        $this->validationRepository         = $validationRepository;
        $this->uploadRepository             = $uploadRepository;

        // Variables related to the services
        $this->entityFetchingService        = $entityFetchingService;
        $this->operatorService              = $operatorService;
        $this->repositoryFactory            = $repositoryFactory;
        $this->docAndOpeTemplatePath        = 'services/operators/docAndOperator.html.twig';
    }




    #[Route('operator/suggest-names', name: 'app_suggest_names')]
    public function suggestNames(Request $request): JsonResponse
    {
        $parsedRequest = json_decode($request->getContent(), true);

        $name = $parsedRequest['name'];

        $rawSuggestions = $this->entityFetchingService->findOperatorByNameLikeForSuggestions($name);

        $teams = $this->entityFetchingService->getTeams();
        $teamIndex = [];
        foreach ($teams as $team) {
            $teamIndex[$team->getId()] = $team->getName();
        }

        $uaps = $this->entityFetchingService->getUaps();
        $uapIndex = [];
        foreach ($uaps as $uap) {
            $uapIndex[$uap->getId()] = $uap->getName();
        }

        foreach ($rawSuggestions as &$suggestion) {
            // Check and assign team name if available
            if (isset($suggestion['team_id']) && isset($teamIndex[$suggestion['team_id']])) {
                $suggestion['team_name'] = $teamIndex[$suggestion['team_id']];
            } else {
                $suggestion['team_name'] = 'nope'; // Or handle it as appropriate
            }

            // Check and assign UAP name if available
            if (isset($suggestion['uap_id']) && isset($uapIndex[$suggestion['uap_id']])) {
                $suggestion['uap_name'] = $uapIndex[$suggestion['uap_id']];
            } else {
                $suggestion['uap_name'] = 'nope'; // Or handle it as appropriate
            }
        }

        // Serialize the entire array of entities at once using groups
        $serializedSuggestions = json_encode($rawSuggestions);

        // Since $serializedSuggestions is a JSON string, return it directly with JsonResponse
        return new JsonResponse($serializedSuggestions, 200, [], true);
    }




    /**
     * Handles deletion requests for operator-related entities.
     *
     * This function delegates the deletion process to the operator service,
     * which handles the actual deletion logic based on the entity type and ID.
     *
     * @param string $entityType   The type of entity to delete (e.g., 'operator', 'team', etc.)
     * @param int $entityId        The ID of the entity to delete
     * @param Request $request     The HTTP request object containing additional data or headers
     *
     * @return Response            A response indicating the result of the deletion operation
     */
    public function deleteActionOperatorController(string $entityType, int $entityId, Request $request): Response
    {
        return $this->operatorService->deleteActionOperatorService($entityType, $entityId, $request);
    }




    //first test of actual page rendering with a validated document and a dynamic form and list of operators and stuff
    /**
     * Renders a page displaying a document and operator information based on a validation ID.
     *
     * This function retrieves a validation by its ID, gets the associated upload,
     * and renders a template with the upload data when accessed via GET request.
     * For other request methods, it redirects to the referring page.
     *
     * @param Request $request      The HTTP request object containing headers and request method
     * @param int $validationId     The ID of the validation to retrieve and process
     *
     * @return Response             A rendered template with upload data or a redirect response
     */
    #[Route('/operator/frontByVal/{validationId}', name: 'app_training_front_by_validation')]
    public function documentAndOperatorByValidation(Request $request, int $validationId): Response
    {
        $referer = $request->headers->get('referer');
        $validation = $this->validationRepository->find($validationId);
        $upload = $validation->getUpload();

        if ($request->getMethod() === 'GET') {
            return $this->render($this->docAndOpeTemplatePath, [
                'upload' => $upload,
            ]);
        } else {
            return $this->redirect($referer);
        }
    }



    //first test of actual page rendering with a validated document and a dynamic form and list of operators and stuff
    /**
     * Renders a page displaying a document and operator information based on an upload ID.
     *
     * This function retrieves an upload by its ID and renders a template with the upload data
     * when accessed via GET request. For other request methods, it redirects to the referring page.
     *
     * @param Request $request  The HTTP request object containing headers and request method
     * @param int $uploadId     The ID of the upload to retrieve and display
     *
     * @return Response         A rendered template with upload data or a redirect response
     */
    #[Route('/operator/frontByUpl/{uploadId}', name: 'app_training_front_by_upload')]
    public function documentAndOperatorByUpload(Request $request, int $uploadId): Response
    {
        $referer = $request->headers->get('referer');
        $upload = $this->uploadRepository->find($uploadId);

        if ($request->getMethod() === 'GET') {
            return $this->render($this->docAndOpeTemplatePath, [
                'upload' => $upload,
            ]);
        } else {
            return $this->redirect($referer);
        }
    }




    /**
     * Renders a page displaying a document and operator information based on an old upload ID.
     *
     * This function retrieves an old upload by its ID, gets the associated current upload entity,
     * and renders a template with the upload data.
     *
     * @param int $oldUploadId  The ID of the old upload to retrieve and process
     *
     * @return Response         A rendered template with upload data
     */
    #[Route('/operator/frontByOldUpl/{oldUploadId}', name: 'app_training_front_by_old_upload')]
    public function documentAndOperatorByOldUpload(int $oldUploadId): Response
    {

        // Issue, originally was only passing the upload docAndOpeTemplatePath, this template
        $oldUpload = $this->repositoryFactory->getRepository('oldUpload')->find($oldUploadId);
        $this->logger->debug('documentAndOperatorByOldUpload :: oldUpload ', [$oldUpload]);
        return $this->render('services/operators/docAndOperatorOldUpload.html.twig', [
            'upload' => $oldUpload->getUpload(),
            'oldUpload' => $oldUpload,
        ]);
    }
}
