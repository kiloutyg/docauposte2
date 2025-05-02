<?php

namespace App\Controller\Operator;

use App\Repository\UploadRepository;
use App\Repository\ValidationRepository;

use App\Service\EntityFetchingService;
use App\Service\OperatorService;

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


    public function __construct(

        LoggerInterface                 $logger,

        // Repository classes
        ValidationRepository            $validationRepository,
        UploadRepository                $uploadRepository,

        // Services classes
        EntityFetchingService           $entityFetchingService,
        OperatorService                 $operatorService,

    ) {
        $this->logger                       = $logger;

        // Variables related to the repositories
        $this->validationRepository         = $validationRepository;
        $this->uploadRepository             = $uploadRepository;

        // Variables related to the services
        $this->entityFetchingService        = $entityFetchingService;
        $this->operatorService              = $operatorService;
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




    public function deleteActionOperatorController(string $entityType, int $entityId, Request $request): Response
    {
        return $this->operatorService->deleteActionOperatorService($entityType, $entityId, $request);
    }




    //first test of actual page rendering with a validated document and a dynamic form and list of operators and stuff
    #[Route('/operator/frontByVal/{validationId}', name: 'app_training_front_by_validation')]
    public function documentAndOperatorByValidation(Request $request, int $validationId): Response
    {
        $referer = $request->headers->get('referer');
        $validation = $this->validationRepository->find($validationId);
        $upload = $validation->getUpload();

        if ($request->getMethod() === 'GET') {
            return $this->render('services/operators/docAndOperator.html.twig', [
                'upload' => $upload,
            ]);
        } else {
            return $this->redirect($referer);
        }
    }



    //first test of actual page rendering with a validated document and a dynamic form and list of operators and stuff
    #[Route('/operator/frontByUpl/{uploadId}', name: 'app_training_front_by_upload')]
    public function documentAndOperatorByUpload(Request $request, int $uploadId): Response
    {
        $referer = $request->headers->get('referer');
        $upload = $this->uploadRepository->find($uploadId);

        if ($request->getMethod() === 'GET') {
            return $this->render('services/operators/docAndOperator.html.twig', [
                'upload' => $upload,
            ]);
        } else {
            return $this->redirect($referer);
        }
    }
}
