<?php

namespace App\Service;

use Psr\Log\LoggerInterface;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

use App\Entity\Upload;

// This class is responsible for the logic of creating and deleting the folder structure used to store the files and organize them in the server filesystem making it easier to manage.
class FolderService
{
    protected $docDir;
    protected $logger;

    public function __construct(
        ParameterBagInterface $params,
        LoggerInterface $logger
    ) {
        $this->docDir = $params->get(name: 'kernel.project_dir') . '/public/doc';
        $this->logger = $logger;
    }

    // This function creates the folder structure for a given entity
    public function folderStructure(string $folderName)
    {
        $this->logger->debug('Creating folder: $folderName: ' . $folderName);

        $folderPath = '';
        // Then the function creates the folder using the dedicated function
        foreach ($this->pathParts($folderName) as $part) {
            $this->logger->debug('Creating folder: $part: ' . $part);

            $folderPath .= '/' . $part;
            $this->logger->debug('Creating folder: $folderPath: ' . $folderPath);

            $this->createFolder($folderPath);
        }
    }

    // This function deletes the folder structure for a given entity
    public function deleteFolderStructure($folderName)
    {
        $this->deleteFolder($this->pathFindingDoc($folderName));
    }

    // This function creates a folder and chmod it to 0755
    public function createFolder($folderPath)
    {
        if (!file_exists($this->docDir . $folderPath)) {
            try {
                mkdir(directory: $this->docDir . $folderPath, permissions: 0755, recursive: false);
            } catch (\Exception $e) {
                $this->logger->error('Failed to create folder: ' . $folderPath, [
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e; // Optionally still rethrow after logging
            }
        }
    }


    // This function deletes a folder
    public function deleteFolder($folderPath)
    {
        if (file_exists($folderPath)) {
            try {
                rmdir($folderPath);
            } catch (\Exception $e) {
                $this->logger->error('Failed to delete folder: ' . $folderPath, [
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e; // Optionally still rethrow after logging
            }
        }
    }

    public function updateFolderStructureAndName($oldName, $newName): bool
    {
        if (rename($this->pathFindingDoc($oldName), $this->pathFindingDoc($newName))) {
            return true;
        } else {
            throw new \Exception('updateFolderStructureAndName: Could not rename the folder');
        }
    }

    public function uploadPath(Upload $upload): string
    {
        return $this->pathFindingDoc($upload->getButton()->getName(), $upload->getFilename());
    }


    public function pathFindingDoc(string $entityName, ?string $addonName = null)
    {
        $parts = $this->pathParts($entityName);
        $path = $this->docDir;
        foreach ($parts as $part) {
            $path .= '/' . $part;
        }
        if ($addonName) {
            $path = $path . '/' . $addonName;
        }
        return $path;
    }


    public function pathParts(string $name): array
    {
        $parts      = explode('.', $name);
        $parts      = array_reverse($parts);
        return $parts;
    }
}
