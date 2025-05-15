<?php

namespace App\Service;

use App\Entity\Uap;
use App\Entity\Operator;

use App\Repository\UapRepository;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UapService extends AbstractController
{
    private $em;
    private $uapRepository;
    public function __construct(
        EntityManagerInterface $em,
        UapRepository $uapRepository,
    ) {
        $this->em = $em;
        $this->uapRepository = $uapRepository;
    }

    

    public function UapInitialization()
    {
        $uap = new Uap();
        $uap->setName('INDEFINI');
        $this->em->persist($uap);
        $this->em->flush();
    }





    /**
     * Update the UAPs associated with an operator
     */
    public function updateOperatorUaps(array $newUapsArray, Operator $operator): void
    {
        if (empty($newUapsArray)) {
            return;
        }

        // Remove operator from all UAPs
        $allUaps = $this->uapRepository->findAll();
        foreach ($allUaps as $uap) {
            $uap->removeOperator($operator);
        }

        // Add operator to selected UAPs
        foreach ($newUapsArray as $newUap) {
            $newUap->addOperator($operator);
            $this->em->persist($newUap);
        }
    }
}
