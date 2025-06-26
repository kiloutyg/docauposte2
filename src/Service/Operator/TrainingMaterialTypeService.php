<?php

namespace App\Service\Operator;

use Psr\Log\LoggerInterface;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Form\Form;

class TrainingMaterialTypeService extends AbstractController
{
    private $em;
    private $logger;

    /**
     * Constructor for the TrainingMaterialType service.
     *
     * @param EntityManagerInterface $em     The entity manager used for database operations
     * @param LoggerInterface        $logger The logger service for logging information
     */
    public function __construct(
        EntityManagerInterface $em,
        LoggerInterface $logger,
    ) {
        $this->em = $em;
        $this->logger = $logger;
    }


    /**
     * Processes the training material type creation form.
     *
     * This function handles the submission of a training material type form,
     * validates the data, converts the name to uppercase, persists it to the database,
     * and returns the name of the created training material type.
     *
     * @param Form $trainingMaterialTypeForm The submitted form containing training material type data
     *
     * @return string The name of the created training material type (in uppercase)
     */
    public function trainingMaterialTypeCreationFormProcessing(Form $trainingMaterialTypeForm): string
    {
        $this->logger->debug(message: 'Processing training material type creation form', context: [$trainingMaterialTypeForm]);
        try {
            $trainingMaterialTypeData = $trainingMaterialTypeForm->getData();
            $trainingMaterialTypeData->setName(strtoupper(string: $trainingMaterialTypeData->getName()));
            $this->em->persist(object: $trainingMaterialTypeData);
            $this->em->flush();
        } finally {
            return $trainingMaterialTypeData->getName();
        }
    }
}
