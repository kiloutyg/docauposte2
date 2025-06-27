<?php

namespace App\Service\Upload;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileTypeService extends AbstractController

{
    public function __construct()
    {
        // Will grow this class to reduce global code duplication
    }

    // Check if the file is of the right type
    public function checkFileType(UploadedFile $file)
    {
        // Define the allowed file extensions
        $allowedExtensions = ['pdf'];

        // Get the file extension
        $extension = $file->guessExtension();
        // Check if the extension is in the list of allowed extensions
        if (!in_array($extension, $allowedExtensions)) {
            return $this->addFlash('error', 'Le fichier doit être un pdf');
        }
        // Check the MIME type of the file
        if ($file->getMimeType() != 'application/pdf') {
            return $this->addFlash('error', 'Le fichier doit être un pdf');
        }
    }
}
