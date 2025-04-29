<?php

namespace App\Service;

use App\Entity\Team;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TeamService extends AbstractController
{
    private $em;
    public function __construct(
        EntityManagerInterface $em,
    ) {
        $this->em = $em;
    }

    public function teamInitialization()
    {
        $team = new Team();
        $team->setName('INDEFINI');
        $this->em->persist($team);
        $this->em->flush();
    }
}
