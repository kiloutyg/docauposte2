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
    /**
     * Validates and processes filenames from uploaded files in the request.
     *
     * This function extracts a file from the request, normalizes its filename,
     * and validates it against a regex pattern to ensure it meets the required
     * naming conventions. For incident files, a unique identifier is added to
     * the filename. If the filename is invalid, an error flash message is added.
     *
     * @param Request $request The HTTP request containing the uploaded file
     * @param string|null $newFilename Optional new filename to use instead of the original
     * @return string|false The processed filename if valid, false otherwise
     */
    public function filenameChecks(Request $request, ?string $newFilename = null): string
    {
        $this->logger->debug('NamingService::filenameChecks() - request', ['request' => $request->request->all()]);

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
        $this->logger->debug('NamingService::filenameChecks() - filename before normalize', ['filename' => $filename]);

        $newName = $this->normalizeFilename($filename);
        $this->logger->debug('NamingService::filenameChecks() - filename after normalize', ['filename' => $newName]);

        $regexFilename = "/^[\p{L}0-9()][\p{L}0-9()_.'-]{2,253}[\p{L}0-9()]$/mu";

        if (!preg_match($regexFilename, $newName)) {
            $this->addFlash('error', 'Le document ' . $filename . ' n\'est pas nommé correctement. ');
            $this->logger->error('NamingService::filenameChecks() - Invalid filename format', ['newName' => $newName]);
            return false;
        }

        if ($entityType === 'incident') {
            return $this->filenameUniqid($filename);
        } else {
            return $newName;
        }
    }

    /**
     * Validates and normalizes a filename.
     *
     * This function takes a filename, normalizes it, and validates it against a regex pattern
     * to ensure it meets the required naming conventions. If the filename is invalid,
     * an error flash message is added.
     *
     * @param string $originalName The original filename to check
     * @param string|null $newFilename Optional new filename to use instead of the original
     * @return string|false The normalized filename if valid, false otherwise
     */
    public function nameChecks(string $originalName, ?string $newFilename = null): string
    {
        if ($newFilename) {
            $filename = $newFilename;
        } else {
            $filename = $originalName;
        }
        $newName = $this->normalizeFilename($filename);
        $regexFilename = "/^[\p{L}0-9()][\p{L}0-9()_.'-]{2,253}[\p{L}0-9()]$/mu";
        if (!preg_match($regexFilename, $newName)) {
            $this->addFlash('error', 'Le document ' . $filename . ' n\'est pas nommé correctement chargé');
            return false;
        }
        return $newName;
    }

    /**
     * Validates and normalizes the upload filename in the request.
     *
     * This function extracts the upload filename from the request, validates and normalizes it
     * using the nameChecks method, then updates the request with the normalized filename.
     *
     * @param Request $request The HTTP request containing the upload data
     * @return void
     */
    public function requestUploadFilenameChecks(Request $request): void
    {
        $this->logger->debug('NamingService::requestUploadFilenameChecks() - request', ['request' => $request->request->all()]);
        $requestArray = $request->request->all();
        $filename = $requestArray['upload']['filename'];
        $newName = $this->nameChecks($filename);
        $requestArray['upload']['filename'] = $newName;
        $request->request->replace($requestArray);
    }

    /**
     * Validates and normalizes the filename for an incident in the request.
     *
     * This function extracts the incident name from the request, validates and normalizes it
     * using the nameChecks method, then updates the request with the normalized filename.
     *
     * @param Request $request The HTTP request containing the incident data
     * @return void
     */
    public function requestIncidentFilenameChecks(Request $request): void
    {
        $requestArray = $request->request->all();
        $newName = $this->nameChecks($requestArray['incident']['name']);
        $requestArray['incident']['name'] = $newName;
        $request->request->replace($requestArray);
    }

    /**
     * Generates a unique filename by appending a unique identifier to the original filename.
     *
     * This function takes a filename, extracts its name and extension components,
     * then adds a unique identifier between them to ensure uniqueness while
     * preserving the original name and extension.
     *
     * @param string $filename The original filename including extension
     * @return string The modified filename with a unique identifier added
     */
    public function filenameUniqid(string $filename)
    {
        $originalName = pathinfo($filename, PATHINFO_FILENAME); // Gets name without extension
        $fileExtension = pathinfo($filename, PATHINFO_EXTENSION); // Gets original extension
        return $originalName . '_' . uniqid('', true) . '.' . $fileExtension;
    }



    /**
     * Normalizes a filename by replacing spaces with underscores, converting accented characters
     * to their non-accented equivalents, and removing any non-compliant characters.
     *
     * This function ensures filenames are compatible with most file systems by:
     * - Converting spaces to underscores
     * - Replacing French accented characters with ASCII equivalents
     * - Converting special characters like '&' and '€' to text representations
     * - Removing any remaining characters that aren't letters, numbers, or allowed symbols
     *
     * @param string $filename The original filename to normalize
     * @return string The normalized filename with only compliant characters
     */
    public function normalizeFilename(string $filename): string
    {
        // Replace spaces with underscores
        $filename = str_replace(' ', '_', $filename);
        $filename = mb_strtolower($filename);

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
            'Œ' => 'OE',

            // Special characters
            '&' => 'et',
            '€' => 'Euros'
            // Add more mappings as needed
        ];

        // Replace accented characters with their non-accented equivalents
        $filename = strtr($filename, $characterMap);

        // Remove any remaining non-compliant characters
        $filename = preg_replace('/[^\p{L}0-9()_.\'\\-]/', '', $filename);
        $this->logger->debug('NamingService::normalizeFilename() - normalized filename', ['filename' => $filename]);

        return $filename;
    }
}
