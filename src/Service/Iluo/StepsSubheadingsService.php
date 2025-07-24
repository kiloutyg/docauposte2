<?php

namespace App\Service\Iluo;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Form\Form;

use Psr\Log\LoggerInterface;

class StepsSubheadingsService extends AbstractController
{

    private $em;
    private $logger;

    public function __construct(
        EntityManagerInterface $em,
        LoggerInterface $logger,
    ) {
        $this->em = $em;
        $this->logger = $logger;
    }


    /**
     * Processes the stepsSubheadings creation form and persists the data to the database.
     *
     * @param Form $stepsSubheadingsForm The form object containing the submitted data.
     *
     * @return string The heading of the processed stepsSubheadings data.
     *
     * @throws Exception If any error occurs during the process.
     */
    public function stepsSubheadingsCreationFormProcessing(Form $stepsSubheadingsForm): string
    {
        $this->logger->debug(message: 'StepsSubheadingsService::stepsSubheadingsCreationFormProcessing - Processing stepsSubheadings creation form', context: [$stepsSubheadingsForm]);
        try {
            $stepsSubheadingsData = $stepsSubheadingsForm->getData();

            $this->em->persist($stepsSubheadingsData);
            $this->em->flush();
        } finally {
            return $stepsSubheadingsData->getHeading();
        }
    }
}
