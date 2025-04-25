<?php

namespace App\Controller\Operator;

use \Psr\Log\LoggerInterface;

use App\Controller\Operator\OperatorBaseController;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Cache\CacheInterface;

use App\Form\OperatorType;
use App\Form\TeamType;
use App\Form\UapType;

use App\Entity\Operator;
use App\Entity\Trainer;
use App\Entity\Team;
use App\Entity\Uap;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use App\Repository\UploadRepository;
use App\Repository\ValidationRepository;
use App\Repository\UapRepository;
use App\Repository\TeamRepository;
use App\Repository\OperatorRepository;
use App\Repository\TrainingRecordRepository;
use App\Repository\TrainerRepository;
use App\Repository\UserRepository;

use App\Service\EntityDeletionService;
use App\Service\EntityFetchingService;
use App\Service\TrainingRecordService;
use App\Service\PdfGeneratorService;
use App\Service\OperatorService;


class OperatorAdminController extends AbstractController
{

    public $em;
    public $request;
    public $logger;
    public $authChecker;
    public $cache;
    public $operatorBaseController;

    // Repository methods
    public $validationRepository;
    public $uploadRepository;
    public $uapRepository;
    public $teamRepository;
    public $operatorRepository;
    public $trainingRecordRepository;
    public $trainerRepository;
    public $userRepository;

    // Services methods
    public $entitydeletionService;
    public $trainingRecordService;
    public $pdfGeneratorService;
    public $entityFetchingService;
    public $operatorService;



    public function __construct(

        EntityManagerInterface          $em,
        LoggerInterface                 $logger,
        AuthorizationCheckerInterface   $authChecker,
        RequestStack                    $requestStack,
        CacheInterface                  $cache,
        OperatorBaseController          $operatorBaseController,

        // Repository classes
        ValidationRepository            $validationRepository,
        UploadRepository                $uploadRepository,
        UapRepository                   $uapRepository,
        TeamRepository                  $teamRepository,
        OperatorRepository              $operatorRepository,
        TrainingRecordRepository        $trainingRecordRepository,
        TrainerRepository               $trainerRepository,
        UserRepository                  $userRepository,

        // Services classes
        EntityDeletionService           $entitydeletionService,
        TrainingRecordService           $trainingRecordService,
        PdfGeneratorService             $pdfGeneratorService,
        EntityFetchingService           $entityFetchingService,
        OperatorService                 $operatorService,

    ) {
        $this->em                           = $em;
        $this->logger                       = $logger;
        $this->authChecker                  = $authChecker;
        $this->request                      = $requestStack->getCurrentRequest();
        $this->cache                        = $cache;
        $this->operatorBaseController           = $operatorBaseController;

        // Variables related to the repositories
        $this->validationRepository         = $validationRepository;
        $this->uploadRepository             = $uploadRepository;
        $this->uapRepository                = $uapRepository;
        $this->teamRepository               = $teamRepository;
        $this->operatorRepository           = $operatorRepository;
        $this->trainingRecordRepository     = $trainingRecordRepository;
        $this->trainerRepository            = $trainerRepository;
        $this->userRepository               = $userRepository;

        // Variables related to the services
        $this->entitydeletionService        = $entitydeletionService;
        $this->trainingRecordService        = $trainingRecordService;
        $this->pdfGeneratorService          = $pdfGeneratorService;
        $this->entityFetchingService        = $entityFetchingService;
        $this->operatorService              = $operatorService;
    }


    #[Route('/operator/admin', name: 'app_operator')]
    public function operatorAdminPage(Request $request): Response
    {
        // // $this->logger->info('search query with full request', $request->request->all());

        $newOperator = new Operator();
        $newOperatorForm = $this->createForm(OperatorType::class, $newOperator);
        $newOperatorForm->handleRequest($request);

        if ($newOperatorForm->isSubmitted() && $newOperatorForm->isValid()) {
            try {
                $newOperatorId = $this->processNewOperator($newOperator, $newOperatorForm, $request);
                $this->addFlash('success', 'L\'opérateur a bien été ajouté');
                // Generate the print detail URL
                $printUrl = $this->generateUrl('app_operator_detail', ['operatorId' => $newOperatorId]);

                // Store the print URL in the session
                $request->getSession()->set('print_operator_url', $printUrl);

                // Redirect to app_operator with a special parameter
                return $this->redirectToRoute('app_operator', ['open_print' => true]);
            } catch (\Exception $e) {
                $this->addFlash('danger', 'L\'opérateur n\'a pas pu être ajouté' . $e->getMessage());
                return $this->redirectToRoute('app_operator');
            }
        }

        if ($request->isMethod('POST') && $request->request->get('search') == 'true') {
            $operators = $this->operatorBaseController->operatorEntitySearch($request);
        } else {
            $operators = [];
        }

        $operatorForms = [];
        $this->logger->info('operators', [$operators]);

        // Create and handle forms
        foreach ($operators as $operator) {
            $this->logger->info('operator', [$operator->getUaps()->getValues()]);

            $operatorForms[$operator->getId()] = $this->createForm(OperatorType::class, $operator, [
                'operator_id' => $operator->getId(),
            ])->createView();
        }

        // $this->logger->info('in operatorBasePage is operatorForms empty: ' . count($operatorForms));

        if (count($operatorForms) === 0) {
            $inActiveOperators = $this->operatorRepository->findDeactivatedOperators();
            foreach ($inActiveOperators as $operator) {
                $operatorForms[$operator->getId()] = $this->createForm(OperatorType::class, $operator, [
                    'operator_id' => $operator->getId(),
                ])->createView();
            }
        }
        // $this->logger->info('message in flashbag', $flashes);

        return $this->render('services/operators/operators_admin.html.twig', [
            'newOperatorForm'   => $newOperatorForm->createView(),
            'operatorForms'     => $operatorForms,
            'teams'             => $this->entityFetchingService->getTeams(),
            'uaps'              => $this->entityFetchingService->getUaps(),
        ]);
    }




    public function processNewOperator(Operator $newOperator, $form, Request $request)
    {

        $trainerBool = $form->get('isTrainer')->getData();
        if ($trainerBool == true) {
            $trainer = new Trainer();
            $trainer->setOperator($newOperator);
            $trainer->setDemoted(false);
            $this->em->persist($trainer);
            $newOperator->setTrainer($trainer);
        } elseif ($trainerBool != true) {
            $trainer = $newOperator->getTrainer();
            $newOperator->setTrainer(null);
            if ($trainer != null) {
                $this->em->remove($trainer);
            }
        };
        $operator = $form->getData();
        $uaps = $operator->getUaps();
        foreach ($uaps as $uap) {
            $uap->addOperator($operator);
            $this->em->persist($uap);
        }
        $this->em->persist($operator);
        $this->em->flush();

        return $operator->getId();
    }



    #[Route('operator/suggest-names', name: 'app_suggest_names')]
    public function suggestNames(Request $request): JsonResponse
    {
        $parsedRequest = json_decode($request->getContent(), true);
        // $this->logger->info('app_suggest_names parsedRequest', $parsedRequest);

        $name = $parsedRequest['name'];

        /////////////// serialized data ////////////////////////
        $rawSuggestions = $this->operatorRepository->findByNameLikeForSuggestions($name);
        // $this->logger->info('app_suggest_names Raw suggestions', $rawSuggestions);

        $teams = $this->teamRepository->findAll();
        $teamIndex = [];
        foreach ($teams as $team) {
            $teamIndex[$team->getId()] = $team->getName();
        }

        $uaps = $this->uapRepository->findAll();
        $uapIndex = [];
        foreach ($uaps as $uap) {
            $uapIndex[$uap->getId()] = $uap->getName();
        }


        foreach ($rawSuggestions as $key => &$suggestion) {
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




    // Individual operator modification controller, used in dev purpose
    #[Route('/operator/edit/{id}', name: 'app_operator_edit')]
    public function editOperatorAction(Request $request, Operator $operator): Response
    {

        if ($request->isMethod('POST') && $request->request->get('search') == 'true') {
            $operators = $this->operatorBaseController->operatorEntitySearch($request);
            $operatorForms = [];
            $this->logger->info('operators', [$operators]);

            // Create and handle forms
            foreach ($operators as $operator) {
                $this->logger->info('operator', [$operator]);

                $operatorForms[$operator->getId()] = $this->createForm(OperatorType::class, $operator, [
                    'operator_id' => $operator->getId(),
                ])->createView();
            }
            $this->logger->info('operatorsForm', [$operatorForms]);
        }

        $form = $this->createForm(OperatorType::class, $operator, [
            'operator_id' => $operator->getId(),
        ]);

        $this->logger->info('operator after form creation', [$operator]);
        $this->logger->info('operator uaps after form creation', [$operator->getUaps()->getValues()]);

        $error = false;
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->operatorService->editOperatorService($form, $operator);
                $this->addFlash('success', 'L\'opérateur a bien été modifié');
            } catch (\Exception $e) {
                $this->addFlash('danger', 'L\'opérateur n\'a pas pu être modifié. Erreur: ' . $e->getMessage());
                $this->logger->error('Error while editing operator in try catch', [$e->getMessage()]);
                $error = true;
            }
        } else {
            $this->logger->error('Error in submitting form while editing operator');
            $error = true;
        }

        $this->logger->info('operator', [$operator]);

        if ($error) {
            $this->logger->info('error true');
            return $this->render('services/operators/admin_component/_adminListOperator.html.twig', [
                'operatorForms' => [$operator->getId() => $form->createView()],
            ]);
        } elseif (isset($operatorForms)) {
            $this->logger->info('operatorsForms isset');
            return $this->render('services/operators/admin_component/_adminListOperator.html.twig', [
                'operatorForms' => $operatorForms,
            ]);
        } else {
            $this->logger->info('there is only operator', [[$operator], [$operator->getUaps()->getValues()]]);
            return $this->render('services/operators/admin_component/_adminListOperator.html.twig', [
                'form' => $form->createView(),
                'operator' => $operator,
                'operatorForms' => $operatorForms = [],

            ]);
        }
    }





    // Route to delete operator from the administrator view
    #[Route('/operator/delete/{id}', name: 'app_operator_delete')]
    public function deleteOperatorAction(int $id): Response
    {

        $result = $this->entitydeletionService->deleteEntity('operator', $id);

        if (!$result) {
            $this->addFlash('danger', 'L\'opérateur n\'a pas pu être supprimé');
            return $this->redirectToRoute('app_operator');
        } else {
            $this->addFlash('success', 'L\'opérateur a bien été supprimé');
            return $this->redirectToRoute('app_operator');
        }
    }




    // Route to print the operator detail in a pdf
    #[Route('/operator/detail/{operatorId}', name: 'app_operator_detail')]
    public function printOpeDetail(int $operatorId)
    {
        $operator = $this->operatorRepository->find($operatorId);
        // $this->logger->info('operator', [$operator]);

        // $pdfContent = $this->pdfGeneratorService->generateOperatorPdf($operator);
        $this->pdfGeneratorService->generateOperatorPdf($operator);

        return true;
    }




    #[Route('/operator/operator_team_or_uap_management', name: 'app_operator_team_or_uap_management')]
    public function operatorTeamUapManagement(Request $request): Response
    {
        $teams = $this->teamRepository->findAll();
        $uaps = $this->uapRepository->findAll();

        if (count($teams) == 0 || count($uaps) == 0) {
            $team = new Team();
            $uap = new Uap();
            $team->setName('INDEFINI');
            $uap->setName('INDEFINI');
            $this->em->persist($team);
            $this->em->persist($uap);
            $this->em->flush();
        }

        $team = new Team();
        $uap = new Uap();
        $teamForm = $this->createForm(TeamType::class, $team);
        $uapForm = $this->createForm(UapType::class, $uap);

        $originUrl = $this->request->headers->get('referer');

        if ($request->getMethod() == 'POST') {
            if (!$this->authChecker->IsGranted('ROLE_ADMIN')) {
                $this->addFlash('danger', 'Vous n\'avez pas les droits pour effectuer cette action');
                return $this->redirect($originUrl);
            }
            $teamForm->handleRequest($request);
            $uapForm->handleRequest($request);
            if ($teamForm->isSubmitted()) {
                if ($teamForm->isValid()) {
                    $team = $teamForm->getData();
                    $this->em->persist($team);
                    $this->em->flush();
                    $this->addFlash('success', 'team has been created');
                    return $this->redirect($originUrl);
                } else {
                    // Validation failed, get the error message and display it
                    $errorMessageTeam = $teamForm->getErrors(true)->current()->getMessage();
                    $this->addFlash('danger', $errorMessageTeam);
                    return $this->redirect($originUrl);
                }
            }
            if ($uapForm->isSubmitted()) {
                if ($uapForm->isValid()) {
                    $uap = $uapForm->getData();
                    $this->em->persist($uap);
                    $this->em->flush();
                    $this->addFlash('success', 'Uap has been created');
                    return $this->redirect($originUrl);
                } else {
                    // Validation failed, get the error message and display it
                    $errorMessageUap = $uapForm->getErrors(true)->current()->getMessage();
                    $this->addFlash('danger', $errorMessageUap);
                    return $this->redirect($originUrl);
                }
            }
        } elseif ($request->getMethod() == 'GET') {
            return $this->render('services/operators/team_uap_operator_management.html.twig', [
                'teams'     => $teams,
                'uaps'      => $uaps,
                'teamForm'  => $teamForm->createView(),
                'uapForm'   => $uapForm->createView()
            ]);
        }
    }

    // Route to delete UAP or Team without breaking operators and training records database
    #[Route('/operator/delete-uap-or-team/{entityType}/{entityId}', name: 'app_delete_uap_or_team')]
    public function deleteUapTeamProperly(string $entityType, int $entityId, Request $request): Response
    {
        $originUrl = $request->headers->get('referer');

        if (!$this->authChecker->IsGranted('ROLE_SUPER_ADMIN')) {
            $this->addFlash('danger', 'Vous n\'avez pas les droits pour effectuer cette action');
            return $this->redirect($originUrl);
        }
        try {
            $this->entitydeletionService->deleteEntity($entityType, $entityId);
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Erreur lors de la suppression de l\'entité' . $e->getMessage());
            return $this->redirectToRoute('app_operator');
        }
        return $this->redirect($originUrl);
    }
}
