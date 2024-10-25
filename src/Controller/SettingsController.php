<?php

namespace App\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

use App\Entity\Settings;

use App\Form\SettingsType;


#[Route('/super_admin')]
// This controller is responsible for rendering the settings interface an managing the logic of the settings interface
class SettingsController extends SuperAdminController
{

    // This function is responsible for rendering the super admin interface
    #[Route('/settings', name: 'app_settings')]
    public function settingsIndex(): Response
    {
        $settings = $this->settingsRepository->findOneBy(['id' => 1]);

        if (!$settings) {
            $settings = new Settings();
        }

        $settingsForm = $this->createForm(SettingsType::class, $settings);

        return $this->render('services/settings/settings.html.twig', [
            'settingsForm' => $settingsForm->createView()
        ]);
    }

    // This function is responsible to pass the settings to the settingsService interface
    #[Route('/settings/update', name: 'app_settings_update')]
    public function settingsUpdate(Request $request): Response
    {

        $this->logger->info('Full request', [
            'request' => $request->request->all()
        ]);

        $referer = $request->headers->get('referer');

        if ($request->isMethod('POST')) {
            try {
                $this->settingsService->updateSettings($request);
            } catch (\Exception $e) {
                $this->logger->error('Error updating settings', [
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $this->redirect($referer);

    }

    // This function is responsible to communicate the settings to the Hotwired/Stimulus controllers
    #[Route('/settings/get', name: 'app_settings_get')]
    public function settingsGet(): JsonResponse
    {
    }
}
