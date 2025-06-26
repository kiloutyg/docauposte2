<?php

namespace App\Service\Operator;

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

    

    /**
     * Initializes a default UAP with the name 'INDEFINI'.
     *
     * This function creates a new UAP entity with a predefined name,
     * persists it to the database, and commits the transaction.
     *
     * @return void
     */
    public function UapInitialization()
    {
        $uap = new Uap();
        $uap->setName('INDEFINI');
        $this->em->persist($uap);
        $this->em->flush();
    }





    /**
     * Updates the UAPs associated with an operator.
     *
     * This function removes the operator from all existing UAPs and then
     * adds the operator to the specified UAPs in the provided array.
     * If the array is empty, the function returns without making any changes.
     *
     * @param array $newUapsArray An array of Uap entities to associate with the operator
     * @param Operator $operator The operator whose UAP associations will be updated
     * @return void
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
