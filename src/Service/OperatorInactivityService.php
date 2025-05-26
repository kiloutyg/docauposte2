<?php

namespace App\Service;

use App\Service\Facade\EntityManagerFacade;

use Psr\Log\LoggerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Service class for managing Operator entities and related business logic.
 *
 * Handles operator lifecycle (creation, update, inactivation, deletion),
 * form processing, search, and initialization of teams and UAPs.
 *
 * @package App\Service
 */
class OperatorInactivityService extends AbstractController
{
    private     $logger;
    private     $projectDir;
    private    $entityManagerFacade;

    public function __construct(
        LoggerInterface                 $logger,
        ParameterBagInterface           $params,
        EntityManagerFacade             $entityManagerFacade,
    ) {
        $this->logger                   = $logger;
        $this->projectDir               = $params->get('kernel.project_dir');

        $this->entityManagerFacade      = $entityManagerFacade;
    }


    /**
     * Checks for operators to set as inactive, mark for deletion, or delete, based on training activity.
     * Ensures the process runs only once per day.
     *
     * @return array|null Returns an array with counts of deactivated and deleted operators, or null if already checked today.
     */
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
                'findDeactivatedOperators' => count($this->entityManagerFacade->findDeactivatedOperators()),
                'toBeDeletedOperators' => count($toBeDeletedOperatorsIds)
            ];
        }
    }


    /**
     * Sets operators with no recent training activity to inactive status.
     *
     * Identifies operators who haven't had training recently and marks them as inactive
     * by setting their inactive date to the current date. Updates the check file
     * to record that this process was run today.
     *
     * @param string    $filePath   Path to the file that tracks when this check was last performed
     * @param \DateTime $today      Current date used to mark operators as inactive
     *
     * @return void
     */
    private function setOperatorToInactive(string $filePath, \DateTime $today)
    {
        $inActiveOperators = $this->entityManagerFacade->findOperatorWithNoRecentTraining();
        if (count($inActiveOperators) > 0) {
            foreach ($inActiveOperators as $operator) {
                $operator->setInactiveSince($today);
                $this->entityManagerFacade->getEntityManager()->persist($operator);
            }
            $this->entityManagerFacade->getEntityManager()->flush();
            file_put_contents($filePath, $today->format('Y-m-d'));
        }
    }


    /**
     * Marks inactive operators for deletion.
     *
     * Identifies operators who are already inactive and marks them for deletion
     * by setting their tobedeleted date to the current date. Updates the check file
     * to record that this process was run today.
     *
     * @param string    $filePath   Path to the file that tracks when this check was last performed
     * @param \DateTime $today      Current date used to mark operators for deletion
     *
     * @return void
     */
    private function setOperatorToBeDeleted(string $filePath, \DateTime $today)
    {
        $operatorSetToBeDeleted = $this->entityManagerFacade->findInActiveOperators();
        if (count($operatorSetToBeDeleted) > 0) {
            foreach ($operatorSetToBeDeleted as $operator) {
                $operator->setTobedeleted($today);
                $this->entityManagerFacade->getEntityManager()->persist($operator);
            }
            $this->entityManagerFacade->getEntityManager()->flush();
            file_put_contents($filePath, $today->format('Y-m-d'));
        }
    }



    /**
     * Permanently deletes operators that were previously marked for deletion.
     *
     * Retrieves all operators marked for deletion, removes them from the database,
     * and updates the check file to record that this process was run today.
     *
     * @param string    $filePath   Path to the file that tracks when this check was last performed
     * @param \DateTime $today      Current date used for updating the check file

     *
     * @return array    Array of IDs of the operators that were deleted
     */
    private function deleteToBeDeletedOperator(string $filePath, \DateTime $today)
    {
        $toBeDeletedOperatorsIds = $this->entityManagerFacade->findOperatorToBeDeleted();
        if (count($toBeDeletedOperatorsIds) > 0) {
            foreach ($toBeDeletedOperatorsIds as $operatorId) {
                $this->entityManagerFacade->deleteEntity('operator', $operatorId);
            }
            $this->entityManagerFacade->getEntityManager()->flush();
            file_put_contents($filePath, $today->format('Y-m-d'));
        }
        return $toBeDeletedOperatorsIds;
    }


}
