<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Routing\Annotation\Route;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use App\Service\EntityFetchingService;

use App\Repository\OperatorRepository;
use App\Repository\UapRepository;

class UapController extends AbstractController
{

    public $logger;
    public $em;
    public $entityFetchingService;

    public $operatorRepository;
    public $uapRepository;

    public function __construct(
        LoggerInterface        $logger,
        EntityManagerInterface $em,
        EntityFetchingService $entityFetchingService,
        OperatorRepository    $operatorRepository,
        UapRepository         $uapRepository
    ) {
        $this->logger = $logger;
        $this->em = $em;
        $this->entityFetchingService = $entityFetchingService;
        $this->operatorRepository = $operatorRepository;
        $this->uapRepository = $uapRepository;
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

    #[Route('/uap/uaptest', name: 'app_uap_change')]
    public function uapTestChange()
    {

        $operator = $this->operatorRepository->find("1276");
        $this->logger->info('operator', [$operator]);
        $currentUaps = $operator->getUaps();
        $this->logger->info('current uaps', [$currentUaps->getValues()]);
        foreach ($currentUaps as $currentUap) {
            $operator->removeUap($currentUap);
            $this->em->persist($operator);
        }
        $this->logger->info('operator uaps', [$operator->getUaps()->getValues()]);
        $uaps = [];
        $uaps[] = $this->uapRepository->find("3");
        $uaps[] = $this->uapRepository->find("4");
        foreach ($uaps as $uap) {
            $operator->addUap($uap);
            $this->em->persist($operator);
        }

        $this->em->flush();
        $this->logger->info('operator uaps', [$operator->getUaps()->getValues()]);

        $this->addFlash('warning', 'Here is the operator uaps, it did do somethin');
        return $this->redirectToRoute('app_base');
    }
}
