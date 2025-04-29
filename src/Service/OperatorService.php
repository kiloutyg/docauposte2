<?php

namespace App\Service;

use App\Entity\Operator;
use App\Entity\Trainer;

use App\Service\EntityDeletionService;
use App\Service\EntityFetchingService;
use App\Service\TeamService;
use App\Service\TrainerService;
use App\Service\UapService;

use Doctrine\ORM\EntityManagerInterface;

use Psr\Log\LoggerInterface;

use Symfony\Component\Form\Form;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class OperatorService extends AbstractController
{
    private     $logger;
    private     $projectDir;
    private     $em;
    private     $validator;

    private     $entityDeletionService;
    private     $entityFetchingService;
    private     $teamService;
    private     $trainerService;
    private     $uapService;


    public function __construct(
        LoggerInterface                 $logger,
        ParameterBagInterface           $params,
        EntityManagerInterface          $em,
        ValidatorInterface              $validator,

        EntityDeletionService           $entityDeletionService,
        EntityFetchingService           $entityFetchingService,
        TeamService                     $teamService,
        TrainerService                  $trainerService,
        UapService                      $uapService
    ) {
        $this->logger                   = $logger;
        $this->projectDir               = $params->get('kernel.project_dir');
        $this->em                       = $em;
        $this->validator                = $validator;

        $this->entityDeletionService    = $entityDeletionService;
        $this->entityFetchingService    = $entityFetchingService;
        $this->teamService              = $teamService;
        $this->trainerService           = $trainerService;
        $this->uapService               = $uapService;
    }



    public function operatorCheckForAutoDelete()
    {
        $today = new \DateTime();
        $fileName = 'checked_for_unactive_operator.txt';
        $filePath = $this->projectDir . '/public/doc/' . $fileName;

        if (!file_exists($filePath) || strpos(file_get_contents($filePath), $today->format('Y-m-d')) === false) {

            $this->setOperatorToInactive($filePath, $today);

            $this->setOperatorToBeDeleted($filePath, $today);

            $toBeDeletedOperatorsIds = $this->deleteToBeDeletedOperator($filePath, $today);

            return [
                'findDeactivatedOperators' => count($this->entityFetchingService->findDeactivatedOperators()),
                'toBeDeletedOperators' => count($toBeDeletedOperatorsIds)
            ];
        }
    }


    private function setOperatorToInactive(string $filePath, \DateTime $today)
    {
        $inActiveOperators = $this->entityFetchingService->findOperatorWithNoRecentTraining();
        if (count($inActiveOperators) > 0) {
            foreach ($inActiveOperators as $operator) {
                $operator->setInactiveSince($today);
                $this->em->persist($operator);
            }
            $this->em->flush();
            file_put_contents($filePath, $today->format('Y-m-d'));
        }
    }


    private function setOperatorToBeDeleted(string $filePath, \DateTime $today)
    {
        $operatorSetToBeDeleted = $this->entityFetchingService->findInActiveOperators();
        if (count($operatorSetToBeDeleted) > 0) {
            foreach ($operatorSetToBeDeleted as $operator) {
                $operator->setTobedeleted($today);
                $this->em->persist($operator);
            }
            $this->em->flush();
            file_put_contents($filePath, $today->format('Y-m-d'));
        }
    }


    private function deleteToBeDeletedOperator(string $filePath, \DateTime $today)
    {
        $toBeDeletedOperatorsIds = $this->entityFetchingService->findOperatorToBeDeleted();
        if (count($toBeDeletedOperatorsIds) > 0) {
            foreach ($toBeDeletedOperatorsIds as $operatorId) {
                $this->entityDeletionService->deleteEntity('operator', $operatorId);
            }
            $this->em->flush();
            file_put_contents($filePath, $today->format('Y-m-d'));
        }
        return $toBeDeletedOperatorsIds;
    }



    public function editOperatorService(Form $form, Operator $operator)
    {
        $this->trainerService->handleTrainerStatus($form->get('isTrainer')->getData(), $operator);
        $this->reactivateOperatorIfNeeded($operator);
        $this->uapService->updateOperatorUaps($form->get('uaps')->getData()->toArray(), $operator);

        $this->em->flush();

        return true;
    }




    /**
     * Reactivate an operator if they were marked for deletion
     */
    private function reactivateOperatorIfNeeded(Operator $operator): void
    {
        if ($operator->getTobedeleted() === null) {
            return;
        }

        $operator->setTobedeleted(null);
        $operator->setLasttraining(new \DateTime());
        $operator->setInactiveSince(null);
    }



    public function autoOperatorNameCheckerFromRequest(Request $request): bool
    {

        $surname = $request->request->get('newOperatorSurname');
        $firstname = $request->request->get('newOperatorFirstname');
        $concatenedOperatorNameNotLower = $firstname . '.' . $surname;
        $concatenedOperatorNameLower = strtolower($concatenedOperatorNameNotLower);
        $operatorName = $request->request->get('newOperatorName');

        return $concatenedOperatorNameLower === $operatorName;
    }



    public function processOperatorFromRequest(string $operatorName, int $operatorCode, int $teamId, int $uapId)
    {

        $team = $this->entityFetchingService->find('team', $teamId);
        $uap = $this->entityFetchingService->find('uap', $uapId);

        $existingOperator = $this->entityFetchingService->findOneBy('operator', ['name' => $operatorName]);
        if ($existingOperator == null) {
            $existingOperator = $this->entityFetchingService->findOneBy('operator', ['code' => $operatorCode]);
        }

        if ($existingOperator != null) {

            if ($existingOperator->getTeam() == $team && $existingOperator->getUaps()->contains($uap)) {
                $this->addFlash('danger', 'Cet opérateur existe déjà dans cette equipe et uap');
                return;
            } else {
                $existingOperator->setTeam($team);
                $existingOperator->addUap($uap);
                $this->em->persist($existingOperator);
                $this->em->flush();
                $this->addFlash('success', 'L\'opérateur a bien été ajouté et son equipe et son UAP ont été modifiées');
                return;
            }
        }

        $operator = new Operator();
        $operator->setName($operatorName);
        $operator->setTeam($team);
        $operator->addUap($uap);
        $operator->setCode($operatorCode);

        $errors = $this->validator->validate($operator);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $violation) {
                // You can use ->getPropertyPath() if you need to show the field name
                $errorMessages[] = $violation->getMessage();
            }

            // Now you have an array of user-friendly messages you can display
            // For example, you can separate them with new lines when displaying in text format:
            $errorsString = implode("\n", $errorMessages);
            $this->logger->error('danger', [$errorsString]);
            return;
        }

        $this->em->persist($operator);
        $this->em->flush();

        $this->addFlash('success', 'L\'opérateur a bien été ajouté');
    }



    public function processNewOperatorFromFormType(Operator $newOperator, Form $form)
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
        }
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



    /**
     * Helper function to find an entity by name or return a default.
     *
     * @param array  $entities
     * @param string $name
     * @param string $defaultName
     *
     * @return object
     */
    public function findEntityByName(array $entities, string $name, string $defaultName)
    {
        foreach ($entities as $entity) {
            if ($entity->getName() === $name) {
                return $entity;
            }
        }
        foreach ($entities as $entity) {
            if ($entity->getName() === $defaultName) {
                return $entity;
            }
        }
        throw new \Exception('Default entity not found');
    }





    public function operatorEntitySearch(Request $request): array
    {
        if ($request->getContentTypeFormat() == 'json') {
            $data = json_decode($request->getContent(), true);
            $name       = $data['search_name'];
            $code       = $data['search_code'];
            $team       = $data['search_team'];
            $uap        = $data['search_uap'];
            $trainer    = $data['search_trainer'];
        } else {
            $name       = $request->request->get('search_name');
            $code       = $request->request->get('search_code');
            $team       = $request->request->get('search_team');
            $uap        = $request->request->get('search_uap');
            $trainer    = $request->request->get('search_trainer');
        }
        return $this->entityFetchingService->findBySearchQuery($name, $code, $team, $uap, $trainer);
    }




    public function operatorTeamUapFormManagement(Form $uapForm, Form $teamForm, Request $request): void
    {
        $teamForm->handleRequest($request);
        $uapForm->handleRequest($request);
        if ($teamForm->isSubmitted()) {
            if ($teamForm->isValid()) {
                $team = $teamForm->getData();
                $this->em->persist($team);
                $this->em->flush();
                $this->addFlash('success', 'team has been created');
            } else {
                // Validation failed, get the error message and display it
                $errorMessageTeam = $teamForm->getErrors(true)->current()->getMessage();
                $this->addFlash('danger', $errorMessageTeam);
                $this->logger->error('Error while creating team', [$errorMessageTeam]);
            }
        }
        if ($uapForm->isSubmitted()) {
            if ($uapForm->isValid()) {
                $uap = $uapForm->getData();
                $this->em->persist($uap);
                $this->em->flush();
                $this->addFlash('success', 'Uap has been created');
            } else {
                // Validation failed, get the error message and display it
                $errorMessageUap = $uapForm->getErrors(true)->current()->getMessage();
                $this->addFlash('danger', $errorMessageUap);
                $this->logger->error('Error while creating UAP', [$errorMessageUap]);
            }
        }
    }



    public function teamUapInitialization(): void
    {
        if (count($this->entityFetchingService->getTeams()) == 0 || $this->entityFetchingService->findOneBy('team', ['name' => 'INDEFINI']) == null) {
            $this->teamService->teamInitialization();
        }
        if (count($this->entityFetchingService->getUaps()) == 0 || $this->entityFetchingService->findOneBy('uap', ['name' => 'INDEFINI']) == null) {
            $this->uapService->uapInitialization();
        }
    }




    public function deleteActionOperatorService(string $entityType, int $entityId, ?Request $request = null): Response
    {
        $result = $this->entityDeletionService->deleteEntity($entityType, $entityId);
        $originUrl = $request->headers->get('referer') ?? 'app_base';

        if (!$result) {
            $this->addFlash('danger',  $entityType . ' n\'a pas pu être supprimé');
            return $this->redirectToRoute($originUrl);
        } else {
            $this->addFlash('success', $entityType . ' a bien été supprimé');
            return $this->redirectToRoute($originUrl);
        }
    }
}
