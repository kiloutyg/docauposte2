<?php

namespace App\Service;


use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class FolderCreationService
{
    protected $public_dir;

    public function __construct(
        ParameterBagInterface $params,
    ) {
        $this->public_dir = $params->get('kernel.project_dir') . '/public';
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