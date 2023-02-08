<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class LineController extends AbstractController

{
    #[Route('/api/line/{id<\d+>}', methods: ['GET'], name: 'api_poduct_get_line')]
    public function getLine(int $id, LoggerInterface $logger): Response
    {
        // TODO query the database
        $line = [
            'id' => $id,
            'name' => 'Waterfalls',
            'url' => 'http://symfonycasts.s3.amazonaws.com/sample.mp3',
        ];

        $logger->info('Returning API response for Line {line}', [
            'line' => $id, 
        ]);

        return $this->json($line);
    }
}