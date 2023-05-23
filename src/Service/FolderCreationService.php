<?php

namespace App\Service;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class FolderCreationService extends AbstractController
{
    protected $projectDir;
    protected $public_dir;

    public function __construct(
        ParameterBagInterface $params,
    ) {
        $this->projectDir            = $params->get('kernel.project_dir');
        $this->public_dir            = $this->projectDir . '/public';
    }
    public function folderStructure(string $folderName)
    {
        $parts = explode('.', $folderName);
        $parts = array_reverse($parts);
        $folderPath = $this->public_dir . '/doc';

        foreach ($parts as $part) {
            $folderPath .= '/' . $part;
            $this->createFolder($folderPath);
        }
    }

    public function createFolder($folderPath)
    {
        if (!file_exists($folderPath)) {
            mkdir($folderPath, 0777, true);
        }
    }
}