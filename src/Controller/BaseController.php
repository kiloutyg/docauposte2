<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


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
    protected $security;
    protected $passwordHasher;
    protected $requestStack;

    public function __construct(ZoneRepository $zoneRepository, ProductLineRepository $productLineRepository, RoleRepository $roleRepository, UserRepository $userRepository, DocumentRepository $documentRepository, EntityManagerInterface $em, RequestStack $requestStack, Security $security, UserPasswordHasherInterface $passwordHasher)
    {
        $this->zoneRepository        = $zoneRepository;
        $this->productLineRepository = $productLineRepository;
        $this->roleRepository        = $roleRepository;
        $this->userRepository        = $userRepository;
        $this->documentRepository    = $documentRepository;
        $this->em                    = $em;
        $this->requestStack          = $requestStack;
        $this->security              = $security;
        $this->passwordHasher        = $passwordHasher;
        $this->request               = $this->requestStack->getCurrentRequest();
    }

}