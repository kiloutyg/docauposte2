<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Psr\Log\LoggerInterface;

// This class is used to manage the uploads files and logic
class NamingService extends AbstractController
{
    private $logger;
    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }
    public function filenameChecks(Request $request, ?string $newFilename = null): string
    {
        $this->logger->info('NamingService::filenameChecks() - request', ['request' => $request->request->all()]);

        if ($request->files->get('file')) {
            $file = $request->files->get('file');
            $entityType = 'upload';
        } elseif ($request->files->get('incident_file')) {
            $file = $request->files->get('incident_file');
            $entityType = 'incident';
        }
        if ($newFilename) {
            $filename = $newFilename;
        } else {
            $filename = $file->getClientOriginalName();
        }
        $this->logger->info('NamingService::filenameChecks() - filename before normalize', ['filename' => $filename]);
        $newName = $this->normalizeFilename($filename);
        $this->logger->info('NamingService::filenameChecks() - filename after normalize', ['filename' => $newName]);
        $regexFilename = "/^[()][\p{L}0-9][\p{L}0-9()_.'-]{2,253}[\p{L}0-9]$/mu";
        if (!preg_match($regexFilename, $newName)) {
            $this->addFlash('error', 'Le document ' . $filename . ' n\'est pas nommé correctement');
            $this->logger->error('NamingService::filenameChecks() - Invalid filename format', ['filename' => $filename]);
            return false;
        }

        if ($entityType === 'incident') {
            return $this->filenameUniqid($filename);
        } else {
            return $newName;
        }
    }

    public function nameChecks(string $originalName, ?string $newFilename = null): string
    {
        if ($newFilename) {
            $filename = $newFilename;
        } else {
            $filename = $originalName;
        }
        $newName = $this->normalizeFilename($filename);
        $regexFilename = "/^[()][\p{L}0-9][\p{L}0-9()_.'-]{2,253}[\p{L}0-9]$/mu";
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

    public function filenameUniqid(string $filename)
    {
        $originalName = pathinfo($filename, PATHINFO_FILENAME); // Gets name without extension
        $fileExtension = pathinfo($filename, PATHINFO_EXTENSION); // Gets original extension
        return $originalName . '_' . uniqid('', true) . '.' . $fileExtension;
    }



    public function normalizeFilename(string $filename): string
    {
        // Replace spaces with underscores
        $filename = str_replace(' ', '_', $filename);

        // Define character mappings for French accented characters
        $characterMap = [
            // Lowercase accented vowels
            'à' => 'a',
            'á' => 'a',
            'â' => 'a',
            'ã' => 'a',
            'ä' => 'a',
            'å' => 'a',
            'è' => 'e',
            'é' => 'e',
            'ê' => 'e',
            'ë' => 'e',
            'ì' => 'i',
            'í' => 'i',
            'î' => 'i',
            'ï' => 'i',
            'ò' => 'o',
            'ó' => 'o',
            'ô' => 'o',
            'õ' => 'o',
            'ö' => 'o',
            'ø' => 'o',
            'ù' => 'u',
            'ú' => 'u',
            'û' => 'u',
            'ü' => 'u',
            'ý' => 'y',
            'ÿ' => 'y',
            'ñ' => 'n',
            'ç' => 'c',
            'æ' => 'ae',
            'œ' => 'oe',

            // Uppercase accented vowels
            'À' => 'A',
            'Á' => 'A',
            'Â' => 'A',
            'Ã' => 'A',
            'Ä' => 'A',
            'Å' => 'A',
            'È' => 'E',
            'É' => 'E',
            'Ê' => 'E',
            'Ë' => 'E',
            'Ì' => 'I',
            'Í' => 'I',
            'Î' => 'I',
            'Ï' => 'I',
            'Ò' => 'O',
            'Ó' => 'O',
            'Ô' => 'O',
            'Õ' => 'O',
            'Ö' => 'O',
            'Ø' => 'O',
            'Ù' => 'U',
            'Ú' => 'U',
            'Û' => 'U',
            'Ü' => 'U',
            'Ý' => 'Y',
            'Ñ' => 'N',
            'Ç' => 'C',
            'Æ' => 'AE',
            'Œ' => 'OE'
        ];

        // Replace accented characters with their non-accented equivalents
        $filename = strtr($filename, $characterMap);

        // Remove any remaining non-compliant characters
        $filename = preg_replace('/[^\p{L}0-9()_.\'\\-]/', '', $filename);

        return $filename;
    }
}
