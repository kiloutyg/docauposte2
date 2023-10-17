<?php

namespace App\Service;

use App\Repository\ButtonRepository;
use App\Repository\CategoryRepository;
use App\Repository\ProductLineRepository;
use App\Repository\ZoneRepository;
use Doctrine\ORM\EntityManagerInterface;

class ViewsModificationService
{
    private $em;
    private $zoneRepository;
    private $productLineRepository;
    private $categoryRepository;
    private $buttonRepository;

    public function __construct(
        EntityManagerInterface $em,
        ZoneRepository $zoneRepository,
        ProductLineRepository $productLineRepository,
        CategoryRepository $categoryRepository,
        ButtonRepository $buttonRepository
    ) {
        $this->em = $em;
        $this->zoneRepository = $zoneRepository;
        $this->productLineRepository = $productLineRepository;
        $this->categoryRepository = $categoryRepository;
        $this->buttonRepository = $buttonRepository;
    }

    public function updateSortOrder($entity)
    {
        $entityId = $entity->getId();
        $entity->setSortOrder($entityId);
        $this->em->persist($entity);
        $this->em->flush($entity);
    }
}