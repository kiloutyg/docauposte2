<?php

namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class ErrorService extends AbstractController
{
    public function __construct() {}

    public function errorRedirectByOrgaEntityType(string $entityType = null): Response
    {

        $this->addFlash('warning', 'This ' . $entityType . ' does not exist.');
        return $this->redirectToRoute('app_base');
    }
}
