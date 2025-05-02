<?php

namespace App\Service\Facade;

use App\Service\IncidentService;
use App\Service\UploadService;
use App\Service\FolderService;

class ContentManagerFacade
{
    private $incidentService;
    private $uploadService;
    private $folderService;

    public function __construct(
        IncidentService $incidentService,
        UploadService $uploadService,
        FolderService $folderService
    ) {
        $this->incidentService = $incidentService;
        $this->uploadService = $uploadService;
        $this->folderService = $folderService;
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
}
