<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Routing\Annotation\Route;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use App\Service\EntityFetchingService;

class UapController extends AbstractController
{

    public $em;
    public $entityFetchingService;

    public function __construct(
        EntityManagerInterface $em,
        EntityFetchingService $entityFetchingService
    ) {
        $this->em = $em;
        $this->entityFetchingService = $entityFetchingService;
    }

    #[Route('/uap/uapchange', name: 'app_uap_change')]
    public function uapChange()
    {
        $operators = $this->entityFetchingService->getOperators();
        foreach ($operators as $operator) {
            $currentUap = $operator->getUap();
            $operator->addUap($currentUap);
            $this->em->persist($operator);
        }
        $this->em->flush();
        return $this->redirectToRoute('app_base');
    }
}