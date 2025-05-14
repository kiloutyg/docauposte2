<?php

namespace App\Controller\Operator;

use App\Service\EntityFetchingService;
use App\Service\OperatorService;

use \Psr\Log\LoggerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\Routing\Annotation\Route;

class OperatorCheckersController extends AbstractController
{

    public $logger;
    public $entityFetchingService;
    public $operatorService;



    public function __construct(
        LoggerInterface                 $logger,
        EntityFetchingService           $entityFetchingService,
        OperatorService                 $operatorService,

    ) {

        $this->logger                       = $logger;

        // Variables related to the services
        $this->entityFetchingService        = $entityFetchingService;
        $this->operatorService              = $operatorService;
    }




    #[Route('/operator/check-duplicate-by-name', name: 'app_operator_check_duplicate_by_name', methods: ['POST'])]
    public function checkDuplicateOperatorByName(Request $request): JsonResponse
    {
        return $this->checkDuplicate('name', json_decode($request->getContent(), true));
    }





    #[Route('/operator/check-duplicate-by-code', name: 'app_operator_check_duplicate_by_code', methods: ['POST'])]
    public function checkDuplicateOperatorByCode(Request $request): JsonResponse
    {
        return $this->checkDuplicate('code', json_decode($request->getContent(), true));
    }



    private function checkDuplicate(string $field, array $parsedRequest): JsonResponse
    {
        $operatorFieldValue = $parsedRequest['value'];
        $existingOperator = $this->entityFetchingService->findOneBy('operator', [$field  => $operatorFieldValue]);

        if ($existingOperator !== null) {
            return new JsonResponse([
                'found' => true,
                'field' => $field,
                'value' => $operatorFieldValue,
                'message' => "Un opérateur avec ce $field existe déjà",
                'operator' => [
                    'id' => $existingOperator->getId(),
                ]
            ]);
        }
        return new JsonResponse([
            'found' => false,
            'field' => $field,
            'value' => $operatorFieldValue,
            'message' => "Aucun opérateur avec ce $field n'existe pas"
        ]);
    }




    // Route to check the operator to validate the training form and make the trained button appear
    #[Route('operator/check-entered-code-against-operator-code/{teamId}/{uapId}', name: 'app_check_entered_code_against_operator_code')]
    public function checkEnteredCodeAgainstOperatorCode(Request $request, int $teamId, int $uapId): JsonResponse
    {
        $parsedRequest = json_decode($request->getContent(), true);
        $enteredCode = (string)$parsedRequest['code'];
        $operatorId = (int)$parsedRequest['operatorId'];
        $controllerOperator = $this->entityFetchingService->findOperatorByCodeAndTeamAndUap($enteredCode, $teamId, $uapId);

        if ($controllerOperator != null) {
            $controllerOperatorId = $controllerOperator->getId();
            $controllerOperatorId === $operatorId ? $operator = $controllerOperator : $operator = null;
            if ($operator !== null) {
                // Found operator
                return new JsonResponse([
                    'found' => true,
                    'operator' => [
                        'id' => $operator->getId(),
                        'name' => $operator->getName(),
                        'code' => $operator->getCode(),
                        'team' => $operator->getTeam()->getName(),
                        'uap' => $operator->getUaps()->first()->getName(),
                    ]
                ]);
            }
        }
        // No operator found
        return new JsonResponse([
            'found' => false,
            'message' => 'Aucun opérateur avec ce code n\'existe dans cette équipe et cette UAP'
        ]);
    }






    // Route to check if a code exist in the database and then return a boolean
    #[Route('operator/check-if-code-exist', name: 'app_check_if_code_exist')]
    public function checkIfCodeExist(Request $request): JsonResponse
    {
        $this->logger->info('Checking if code exist, full request: ', [$request->request->all()]);

        $parsedRequest = json_decode($request->getContent(), true);

        $enteredCode = $parsedRequest['code'];
        $existingOperator = $this->entityFetchingService->findOneBy('operator', ['code' => $enteredCode]);

        if ($existingOperator !== null) {
            return new JsonResponse([
                'found' => true,
            ]);
        } else {
            return new JsonResponse([
                'found' => false,
            ]);
        }
    }





    // Route to check if a trainer exist by name and code
    #[Route('operator/check-if-trainer-exist', name: 'app_check_if_trainer_exist')]
    public function checkIfTrainerExist(Request $request): JsonResponse
    {
        $found = false;
        $name = null;
        $code = null;
        $trainerId = null;

        $parsedRequest = json_decode($request->getContent(), true);

        key_exists('code', $parsedRequest) ? $enteredCode = $parsedRequest['code'] : $enteredCode = null;
        key_exists('name', $parsedRequest) ? $enteredName = $parsedRequest['name'] : $enteredName = null;

        if ($enteredCode != null) {
            $existingOperator = $this->entityFetchingService->findOneBy('operator', ['code' => $enteredCode, 'name' => $enteredName, 'isTrainer' => true]);
            if ($existingOperator !== null) {
                $found = true;
                $name = $existingOperator->getName();
                $code = $existingOperator->getCode();
                $trainerId = $existingOperator->getId();
            }
        } else {
            $existingOperator = $this->entityFetchingService->findOneBy('operator', ['name' => $enteredName, 'isTrainer' => true]);
            if ($existingOperator !== null) {
                $found = true;
                $name = $existingOperator->getName();
                $code = $existingOperator->getCode();
            }
        }
        return new JsonResponse([
            'found'         => $found ?? false,
            'name'          => $name ?? null,
            'code'          => $code ?? null,
            'trainerId'     => $trainerId ?? null,
        ]);
    }
}
