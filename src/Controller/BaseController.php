<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use  \Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;



use App\Repository\ZoneRepository;
use App\Repository\ProductLineRepository;
use App\Repository\UserRepository;
use App\Repository\UploadRepository;
use App\Repository\CategoryRepository;
use App\Repository\ButtonRepository;
use App\Repository\SignatureRepository;

use App\Entity\Zone;
use App\Entity\ProductLine;
use App\Entity\User;
use App\Entity\Upload;
use App\Entity\Category;
use App\Entity\Button;
use App\Entity\Signature;

use App\Service\EntityDeletionService;
use App\Service\AccountService;
use App\Service\UploadsService;
use App\Service\FolderCreationService;



#[Route('/', name: 'app_')]



class BaseController extends AbstractController
{
    protected $zoneRepository;
    protected $productLineRepository;
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
    protected $uploadsService;
    protected $logger;
    protected $loggerInterface;
    protected $projectDir;
    protected $public_dir;
    protected $folderCreationService;





    public function __construct(
        UploadRepository $uploadRepository,
        ZoneRepository $zoneRepository,
        ProductLineRepository $productLineRepository,
        UserRepository $userRepository,
        EntityManagerInterface $em,
        RequestStack $requestStack,
        Security $security,
        UserPasswordHasherInterface $passwordHasher,
        CategoryRepository $categoryRepository,
        ButtonRepository $buttonRepository,
        SignatureRepository $signatureRepository,
        EntityDeletionService $entitydeletionService,
        AccountService $accountService,
        UploadsService $uploadsServices,
        LoggerInterface $loggerInterface,
        ParameterBagInterface $params,
        FolderCreationService $folderCreationService,

    ) {

        $this->uploadRepository      = $uploadRepository;
        $this->zoneRepository        = $zoneRepository;
        $this->productLineRepository = $productLineRepository;
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
        $this->uploadsService        = $uploadsServices;
        $this->logger                = $loggerInterface;
        $this->request               = $this->requestStack->getCurrentRequest();
        $this->session               = $this->requestStack->getSession();
        $this->projectDir            = $params->get('kernel.project_dir');
        $this->public_dir            = $this->projectDir . '/public';
        $this->folderCreationService = $folderCreationService;
    }
}