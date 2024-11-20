<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

use App\Service\SettingsService;
use App\Service\EntityFetchingService;

class VariableExtension extends AbstractExtension implements GlobalsInterface
{
    private $settingsService;
    private $entityFetchingService;

    public function __construct(
        SettingsService             $settingsService,
        EntityFetchingService       $entityFetchingService,
    ) {
        $this->settingsService          = $settingsService;
        $this->entityFetchingService    = $entityFetchingService;
    }

    public function getGlobals(): array
    {
        $usersExist = false;
        if (!empty($this->entityFetchingService->getUsers())) {
            $usersExist = true;
        }
        return [
            'settings'      => $this->settingsService->getSettings(),
            'usersExist'    => $usersExist,
        ];
    }
}
