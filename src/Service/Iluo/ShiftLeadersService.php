<?php

namespace App\Service\Iluo;

use Psr\Log\LoggerInterface;

use App\Entity\ShiftLeaders;

use App\Repository\ShiftLeadersRepository;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Form\Form;

class ShiftLeadersService extends AbstractController
{
    private $em;
    private $logger;
    private $shiftLeadersRepository;

    /**
     * Constructor for the ShiftLeadersService class.
     *
     * Initializes the service with required dependencies for database operations,
     * logging, and shift leader data access.
     *
     * @param EntityManagerInterface $em The entity manager for database operations
     * @param LoggerInterface $logger The logger service for logging events and errors
     * @param ShiftLeadersRepository $shiftLeadersRepository Repository for shift leader data access
     */
    public function __construct(
        EntityManagerInterface $em,
        LoggerInterface $logger,

        ShiftLeadersRepository $shiftLeadersRepository,
    ) {
        $this->em = $em;
        $this->logger = $logger;

        $this->shiftLeadersRepository = $shiftLeadersRepository;
    }

    /**
     * Processes the shift leader creation form by persisting the data to the database.
     *
     * This method extracts data from the submitted form, persists it to the database,
     * and returns the name of the created shift leader.
     *
     * @param Form $shiftLeaderForm The submitted form containing shift leader data
     * @return string The name of the created shift leader (username or operator name)
     */
    public function shiftLeadersCreationFormProcessing(Form $shiftLeaderForm): string
    {
        $this->logger->debug(message: 'ShiftLeadersService::shiftLeadersCreationFormProcessing - Processing shift leaders creation form', context: [$shiftLeaderForm]);
        try {
            $shiftLeaderData = $shiftLeaderForm->getData();
            $this->logger->debug(message: 'ShiftLeadersService::shiftLeadersCreationFormProcessing - Shift leader data', context: ['shiftLeaderData' => $shiftLeaderData]);

            if ($shiftLeaderData->getOperator() && ($shiftLeaderData->getOperator()->getUser())) {
                $this->logger->debug(message: 'ShiftLeadersService::shiftLeadersCreationFormProcessing - Operator set and linked User entity found');
                $shiftLeaderData->setUser($shiftLeaderData->getOperator()->getUser());
            } elseif ($shiftLeaderData->getUser() && ($shiftLeaderData->getUser()->getOperator())) {
                $this->logger->debug(message: 'ShiftLeadersService::shiftLeadersCreationFormProcessing - User set and linked Operator entity found');
                $shiftLeaderData->setOperator($shiftLeaderData->getUser()->getOperator());
            }
            $this->logger->debug(message: 'ShiftLeadersService::shiftLeadersCreationFormProcessing - Shift after user check', context: ['shiftLeaderData' => $shiftLeaderData]);
            $this->em->persist($shiftLeaderData);
            $this->em->flush();
        } finally {
            $shiftLeaderName = '';
            if ($shiftLeaderData->getUser()) {
                $shiftLeaderName = $shiftLeaderData->getUser()->getUsername();
            } elseif ($shiftLeaderData->getOperator()) {
                $shiftLeaderName = $shiftLeaderData->getOperator()->getName();
            }
            return $shiftLeaderName;
        }
    }
}
