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

    /**
     * Initializes a default team with the name 'INDEFINI'.
     *
     * This function creates a new Team entity, sets its name to 'INDEFINI',
     * persists it to the database, and then flushes the changes.
     *
     * @return void
     */
    public function teamInitialization()
    {
        $team = new Team();
        $team->setName('INDEFINI');
        $this->em->persist($team);
        $this->em->flush();
    }
}
