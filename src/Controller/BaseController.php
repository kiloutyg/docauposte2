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
use App\Entity\Zone;
use App\Entity\ProductLine;
use App\Entity\Role;
use App\Entity\User;
use App\Entity\Document;
use App\Repository\UploadRepository;



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
    protected $uploadRepository;
    protected $session;

    public function __construct(UploadRepository $uploadRepository, ZoneRepository $zoneRepository, ProductLineRepository $productLineRepository, RoleRepository $roleRepository, UserRepository $userRepository, DocumentRepository $documentRepository, EntityManagerInterface $em, RequestStack $requestStack, Security $security, UserPasswordHasherInterface $passwordHasher)
    {

        $this->uploadRepository      = $uploadRepository;
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
        $this->session               = $this->requestStack->getSession();



        if ($this->session->get('id') == null) {
            $this->session->set('id', uniqid());
        } else {
            // check if user is connected to update session with user
            $user = $this->security->getUser();
            if ($user != null) {
                $user    = $userRepository->findOneBy(['username' => $user->getUserIdentifier()]);
                $role   = $roleRepository->findOneBy(['id' => $user->getRoles()]);
                $this->session->set('user', $user);
                $this->session->set('role', $role);
            }
            $user = null;
        }
    }
    public function redirectToLogin()
    {
        $this->si->set('previous_url', $this->generateUrl('app_master')); // Store the current URL in the session
        return $this->redirectToRoute('app_login');
    }
}