<?php

namespace App\Controller\Support;

use  \Psr\Log\LoggerInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Service\OperatorService;
use App\Service\SettingsService;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/super_admin')]
// This controller is responsible for rendering the settings interface an managing the logic of the settings interface
class SettingsController extends AbstractController
{
    private $logger;
    private $operatorService;
    private $settingsService;

    public function __construct(
        LoggerInterface                 $logger,
        OperatorService                 $operatorService,
        SettingsService                 $settingsService
    ) {
        $this->logger                       = $logger;
        $this->operatorService              = $operatorService;
        $this->settingsService              = $settingsService;
    }


    // This function is responsible for rendering the super admin interface
    /**
     * Renders the settings interface for super admin users.
     *
     * This function retrieves the settings form from the settings service
     * and passes it to the template for rendering.
     *
     * @return Response A Response instance containing the rendered settings page
     */
    #[Route('/settings', name: 'app_settings')]
    public function settingsIndex(): Response
    {
        $settingsForm = $this->settingsService->getSettingsForm();

        return $this->render('services/settings/settings.html.twig', [
            'settingsForm' => $settingsForm->createView()
        ]);
    }


    // This function is responsible to pass the settings to the settingsService interface
    /**
     * Updates application settings based on submitted form data.
     *
     * This function processes POST requests containing settings data,
     * passes them to the settings service for processing, and provides
     * feedback to the user via flash messages.
     *
     * @param Request $request The HTTP request containing the submitted settings data
     * @return Response A redirect response that returns the user to the referring page
     */
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


    /**
     * Forces the deletion of operators marked as 'to be deleted'.
     *
     * This function triggers the force deletion of operators that have been marked as 'to be deleted'
     * in the database. It logs any errors that occur during the process and provides feedback
     * to the user via flash messages.
     *
     * @return Response A redirect response to the 'app_super_admin' route
     * @throws \Exception If an error occurs during the force deletion process
     */
    #[Route(path: '/tabularasa', name: 'app_tabularasa')]
    public function forceDeleteToBeDeletedOperator(): Response
    {
        $this->logger->info('Force deleting operators marked as to be deleted');
        $numberOfOperatorDeleted = 0;
        try {
            $numberOfOperatorDeleted = $this->operatorService->forceDeleteToBeDeletedOperator();
        } catch (\Exception $e) {
            $this->logger->error('Error force deleting operators', [
                'error' => $e->getMessage()
            ]);
            $this->addFlash('error', 'Erreur lors de la suppression des opérateurs à supprimer' . $e->getMessage());
        }

        $this->addFlash('success', $numberOfOperatorDeleted . ' opérateurs ont été supprimés');

        return $this->redirectToRoute('app_super_admin');
    }
}
