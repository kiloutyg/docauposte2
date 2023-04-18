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
use App\Repository\UploadRepository;
use App\Repository\CategoryRepository;
use App\Repository\ButtonRepository;
use App\Repository\SignatureRepository;

use App\Entity\Zone;
use App\Entity\ProductLine;
use App\Entity\Role;
use App\Entity\User;
use App\Entity\Upload;
use App\Entity\Category;
use App\Entity\Button;
use App\Entity\Signature;

use App\Service\EntityDeletionService;
use App\Service\AccountService;



#[Route('/', name: 'app_')]



class BaseController extends AbstractController
{
    protected $zoneRepository;
    protected $productLineRepository;
    protected $roleRepository;
    protected $userRepository;
    protected $em;
    protected $request;
    protected $security;
    protected $passwordHasher;
    protected $requestStack;
    protected $uploadRepository;
    protected $session;
    protected $categoryRepository;
    protected $buttonRepository;
    protected $signatureRepository;

    protected $entitydeletionService;
    protected $accountService;



    public function __construct(
        UploadRepository $uploadRepository,
        ZoneRepository $zoneRepository,
        ProductLineRepository $productLineRepository,
        RoleRepository $roleRepository,
        UserRepository $userRepository,
        EntityManagerInterface $em,
        RequestStack $requestStack,
        Security $security,
        UserPasswordHasherInterface $passwordHasher,
        CategoryRepository $categoryRepository,
        ButtonRepository $buttonRepository,
        SignatureRepository $signatureRepository,
        EntityDeletionService $entitydeletionService,
        AccountService $accountService
    ) {

        $this->uploadRepository      = $uploadRepository;
        $this->zoneRepository        = $zoneRepository;
        $this->productLineRepository = $productLineRepository;
        $this->roleRepository        = $roleRepository;
        $this->userRepository        = $userRepository;
        $this->categoryRepository    = $categoryRepository;
        $this->buttonRepository      = $buttonRepository;
        $this->signatureRepository   = $signatureRepository;
        $this->em                    = $em;
        $this->requestStack          = $requestStack;
        $this->security              = $security;
        $this->passwordHasher        = $passwordHasher;
        $this->entitydeletionService = $entitydeletionService;
        $this->accountService        = $accountService;
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