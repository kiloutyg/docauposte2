<?php

namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Psr\Log\LoggerInterface;

use App\Repository\OperatorRepository;



class OperatorService extends AbstractController
{
    protected $logger;
    protected $operatorRepository;

    public function __construct(
        LoggerInterface $logger,
        OperatorRepository $operatorRepository
    ) {
        $this->logger = $logger;
        $this->operatorRepository = $operatorRepository;
    }
}
