<?php

namespace App\Service\Iluo;

use Psr\Log\LoggerInterface;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

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



    public function trainingMaterialTypeCreationFormProcessing(Form $trainingMaterialTypeForm, ?Request $request = null): string
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
