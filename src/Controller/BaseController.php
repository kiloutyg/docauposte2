<?php

namespace App\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


use App\Repository\ZoneRepository;
use App\Repository\ProductLineRepository;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\Repository\DocumentRepository;


#[Route('/', name: 'app_')]



class BaseController extends AbstractController
{
    protected $zoneRepository;
    protected $productLineRepository;
    protected $roleRepository;
    protected $userRepository;
    protected $documentRepository;
    protected $em;

    public function __construct(ZoneRepository $zoneRepository, ProductLineRepository $productLineRepository, RoleRepository $roleRepository, UserRepository $userRepository, DocumentRepository $documentRepository, EntityManager $em)
    {
        $this->zoneRepository        = $zoneRepository;
        $this->productLineRepository = $productLineRepository;
        $this->roleRepository        = $roleRepository;
        $this->userRepository        = $userRepository;
        $this->documentRepository    = $documentRepository;
        $this->em                    = $em;
    }

    private $categories = [
        [
            'zones' => ['Assemblage' => ['lignes' => ['D41', 'Demontage', 'Enjoliveur']], 'Déchargement Reprise' => ['lignes' => ['D41', 'DEFAUTHEQUE']]]
        ]
    ];

    #[Route('/', name: 'base')]
    public function base(): Response
    {
        return $this->render('base.html.twig', [
            'categories' => $this->categories,
        ]);
    }



}