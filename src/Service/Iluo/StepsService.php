<?php

namespace App\Service\Iluo;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Form\Form;

use Psr\Log\LoggerInterface;

class StepsService extends AbstractController
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
     * Processes the steps creation form and persists the data to the database.
     *
     * @param Form $stepsForm The form containing the steps data.
     *
     * @return string The question from the processed steps data.
     *
     * @throws Exception If any error occurs during the process.
     */
    public function stepsCreationFormProcessing(Form $stepsForm): string
    {
        $this->logger->debug(message: 'StepsService::stepsCreationFormProcessing - Processing steps creation form', context: [$stepsForm]);
        try {
            $stepsData = $stepsForm->getData();

            $this->em->persist($stepsData);
            $this->em->flush();
        } finally {
            return $stepsData->getQuestion();
        }
    }
}
