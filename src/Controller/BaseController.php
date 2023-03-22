<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
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
    protected $request;

    public function __construct(ZoneRepository $zoneRepository, ProductLineRepository $productLineRepository, RoleRepository $roleRepository, UserRepository $userRepository, DocumentRepository $documentRepository, EntityManagerInterface $em)
    {
        $this->zoneRepository        = $zoneRepository;
        $this->productLineRepository = $productLineRepository;
        $this->roleRepository        = $roleRepository;
        $this->userRepository        = $userRepository;
        $this->documentRepository    = $documentRepository;
        $this->em                    = $em;
    }



    #[Route('/', name: 'base')]
    public function base(): Response
    {
        return $this->render(
            'base.html.twig',
            [
                'zones'        => $this->zoneRepository->findAll(),
                'productLines' => $this->productLineRepository->findAll(),
                'roles'        => $this->roleRepository->findAll(),
                'users'        => $this->userRepository->findAll(),
                'documents'    => $this->documentRepository->findAll(),
            ]
        );
    }

    #[Route('/zone/{id}', name: 'zone')]

    public function zone(string $id = null): Response
    {
        $zone = $this->zoneRepository->findOneBy(['id' => $id]);

        return $this->render(
            'zone.html.twig',
            [

                'zone'         => $zone,
                'productLines' => $this->productLineRepository->findAll(),
                'roles'        => $this->roleRepository->findAll(),
            ]
        );
    }



}