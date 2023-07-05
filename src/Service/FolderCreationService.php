<?php

namespace App\Service;

use Doctrine\ORM\Mapping\Entity;
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

    // public function deleteFolderStructure(Entity $entity)
    public function deleteFolderStructure($folderName)
    {
        // $folderName = getName($entity);

        $parts = explode('.', $folderName);
        $parts = array_reverse($parts);
        $folderPath = $this->public_dir . '/doc';

        foreach ($parts as $part) {
            $folderPath .= '/' . $part;
        }
        $this->deleteFolder($folderPath);
    }

    public function createFolder($folderPath)
    {
        if (!file_exists($folderPath)) {
            mkdir($folderPath, 0777, true);
        }
    }

    public function deleteFolder($folderPath)
    {
        if (file_exists($folderPath)) {
            rmdir($folderPath);
        }
    }
}