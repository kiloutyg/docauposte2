<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Form\Form;

use Symfony\Component\HttpFoundation\Response;

use App\Repository\ProductsRepository;

class ProductsService extends AbstractController
{

    private $em;

    private $productsRepository;

    public function __construct(
        EntityManagerInterface $em,
        ProductsRepository $productsRepository,
    ) {
        $this->em = $em;
        $this->productsRepository = $productsRepository;
    }

    public function productCreationFormProcessing(Form $productForm): string
    {
        try {
            $productData = $productForm->getData();
            $productData->setName(strtoupper($productData->getName()));
            $this->em->persist($productData);
            $this->em->flush();
        } finally {
            return $productData->getName();
        }
    }
}
