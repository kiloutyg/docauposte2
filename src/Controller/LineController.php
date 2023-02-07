<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
// use Symfony\Component\HttpFoundation\JsonResponse;

class LineController extends AbstractController

{
    #[Route('/api/line/{id<\d+>}', name: 'app_PoductLine', methods: ['GET'])]
    public function getLine(int $id, LoggerInterface $logger): Response
    {
        // TODO query the database
        $line = [
            'id' => $id,
            'name' => 'Waterfalls',
            'url' => 'https://symfonycasts.s3.amazonaws.com/sample.mp3',
        ];

        // $logger->info('Returning API response for Line'.$id);
        $logger->info('Returning API response for Line {Line}', [
            'Line' => $id, 
        ]);

        // return new JsonResponse($line);
        return $this->json($line);
    }
}