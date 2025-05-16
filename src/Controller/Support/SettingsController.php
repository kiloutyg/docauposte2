<?php

namespace App\Controller\Support;

use  \Psr\Log\LoggerInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Service\SettingsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/super_admin')]
// This controller is responsible for rendering the settings interface an managing the logic of the settings interface
class SettingsController extends AbstractController
{
    private $logger;
    private $settingsService;

    public function __construct(
        LoggerInterface                 $logger,
        SettingsService                 $settingsService
    ) {
        $this->logger                       = $logger;
        $this->settingsService              = $settingsService;
    }


    // This function is responsible for rendering the super admin interface
    #[Route('/settings', name: 'app_settings')]
    public function settingsIndex(): Response
    {
        $settingsForm = $this->settingsService->getSettingsForm();

        return $this->render('services/settings/settings.html.twig', [
            'settingsForm' => $settingsForm->createView()
        ]);
    }

    
    // This function is responsible to pass the settings to the settingsService interface
    #[Route('/settings/update', name: 'app_settings_update')]
    public function settingsUpdate(Request $request): Response
    {
        $referer = $request->headers->get('referer');

        if ($request->isMethod('POST')) {
            try {
                $response = $this->settingsService->updateSettings($request);
                if ($response) {
                    $this->addFlash('success', 'Paramètres mis à jour avec succès');
                }
            } catch (\Exception $e) {
                $this->logger->error('Error updating settings', [
                    'error' => $e->getMessage()
                ]);
                $this->addFlash('error', 'Erreur lors de la mise à jour des paramètres' . $e->getMessage());
            }
        }
        return $this->redirect($referer);
    }
}
