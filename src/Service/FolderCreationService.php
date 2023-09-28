<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;


// This class is responsible for the logic of creating and deleting the folder structure used to store the files and organize them in the server filesystem making it easier to manage.
class FolderCreationService
{
    protected $public_dir;

    public function __construct(
        ParameterBagInterface $params,
    ) {
        $this->public_dir = $params->get('kernel.project_dir') . '/public';
    }

    // This function creates the folder structure for a given entity
    public function folderStructure(string $folderName)
    {
        // The function obtain the right folder name from the entity name 
        $parts = explode('.', $folderName);
        $parts = array_reverse($parts);
        $folderPath = $this->public_dir . '/doc';

        // Then the function creates the folder using the dedicated function
        foreach ($parts as $part) {
            $folderPath .= '/' . $part;
            $this->createFolder($folderPath);
        }
    }

    // This function deletes the folder structure for a given entity
    public function deleteFolderStructure($folderName)
    {

        $parts = explode('.', $folderName);
        $parts = array_reverse($parts);
        $folderPath = $this->public_dir . '/doc';

        foreach ($parts as $part) {
            $folderPath .= '/' . $part;
        }
        $this->deleteFolder($folderPath);
    }

    // This function creates a folder and chmod it to 0777
    public function createFolder($folderPath)
    {
        if (!file_exists($folderPath)) {
            mkdir($folderPath, 0777, true);
        }
    }

    // This function deletes a folder
    public function deleteFolder($folderPath)
    {
        if (file_exists($folderPath)) {
            rmdir($folderPath);
        }
    }
}