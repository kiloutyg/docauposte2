<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Psr\Log\LoggerInterface;





class ValidationService extends AbstractController
{
    protected $logger;
    protected $em;



    public function __construct(
        LoggerInterface                 $logger,
        EntityManagerInterface          $em

    ) {
        $this->logger                = $logger;
        $this->em                    = $em;
    }
}
