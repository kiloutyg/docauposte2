<?php

namespace App\Service\Facade;

use Symfony\Component\HttpFoundation\Request;

use App\Service\Incident\IncidentService;
use App\Service\Upload\UploadService;
use App\Service\FolderService;
use App\Service\NamingService;

class ContentManagerFacade
{
    private $incidentService;
    private $uploadService;
    private $folderService;
    private $namingService;

    public function __construct(
        IncidentService                     $incidentService,
        UploadService                       $uploadService,
        FolderService                       $folderService,
        NamingService                       $namingService

    ) {
        $this->incidentService              = $incidentService;
        $this->uploadService                = $uploadService;
        $this->folderService                = $folderService;
        $this->namingService                = $namingService;
    }

    public function groupIncidents($incidents)
    {
        return $this->incidentService->groupIncidents($incidents);
    }

    public function groupAllUploads($uploads)
    {
        return $this->uploadService->groupAllUploads($uploads);
    }

    public function folderStructure($name)
    {
        return $this->folderService->folderStructure($name);
    }

    public function requestIncidentFilenameChecks(Request $request)
    {
        return $this->namingService->requestIncidentFilenameChecks($request);
    }

    public function filenameChecks(Request $request, ?string $newFilename = null): string
    {
        return $this->namingService->filenameChecks($request, $newFilename);
    }

    public function requestUploadFilenameChecks(Request $request)
    {
        return $this->namingService->requestUploadFilenameChecks($request);
    }
}
