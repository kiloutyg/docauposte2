<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

// This class is used to manage the uploads files and logic
class NamingService extends AbstractController
{
    public function __construct()
    {
        // Placeholder
    }
    public function filenameChecks(Request $request, ?string $newFilename = null): string
    {
        if ($request->files->get('file')) {
            $file = $request->files->get('file');
        } elseif ($request->files->get('incident_file')) {
            $file = $request->files->get('incident_file');
        }
        if ($newFilename) {
            $filename = $newFilename;
        } else {
            $filename = $file->getClientOriginalName();
        }
        $newName = str_replace(' ', '_', $filename);
        $regexFilename = "/^[\p{L}0-9][\p{L}0-9()_.'-]{2,253}[\p{L}0-9]$/mu";
        if (!preg_match($regexFilename, $newName)) {
            $this->addFlash('error', 'Le document ' . $filename . ' n\'est pas nommé correctement chargé');
            return false;
        }
        return $newName;
    }

    public function nameChecks(string $originalName, ?string $newFilename = null): string
    {
        if ($newFilename) {
            $filename = $newFilename;
        } else {
            $filename = $originalName;
        }
        $newName = str_replace(' ', '_', $filename);
        $regexFilename = "/^[\p{L}0-9][\p{L}0-9()_.'-]{2,253}[\p{L}0-9]$/mu";
        if (!preg_match($regexFilename, $newName)) {
            $this->addFlash('error', 'Le document ' . $filename . ' n\'est pas nommé correctement chargé');
            return false;
        }
        return $newName;
    }

    public function requestUploadFilenameChecks(Request $request): void
    {
        $requestArray = $request->request->all();
        $newName = $this->nameChecks($requestArray['upload']['filename']);
        $requestArray['upload']['filename'] = $newName;
        $request->request->replace($requestArray);
    }

    public function requestIncidentFilenameChecks(Request $request): void
    {
        $requestArray = $request->request->all();
        $newName = $this->nameChecks($requestArray['incident']['name']);
        $requestArray['incident']['name'] = $newName;
        $request->request->replace($requestArray);
    }
}
