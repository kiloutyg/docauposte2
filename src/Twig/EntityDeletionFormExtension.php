<?php

namespace App\Twig;

use App\Form\EntityDeletionType;

use Psr\Log\LoggerInterface;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class EntityDeletionFormExtension extends AbstractExtension
{
    private $logger;
    private $formFactory;
    private $urlGenerator;

    public function __construct(
        LoggerInterface         $logger,
        FormFactoryInterface    $formFactory,
        UrlGeneratorInterface   $urlGenerator
    ) {
        $this->logger           = $logger;

        $this->formFactory      = $formFactory;
        $this->urlGenerator     = $urlGenerator;
    }

    public function getFunctions(): array
    {
        $this->logger->debug('Registering Twig function delete_form');
        return [
            new TwigFunction('delete_form', [$this, 'createDeleteForm'], [
                'is_safe' => ['html'],
                'needs_environment' => true,
            ]),
        ];
    }

    public function createDeleteForm(\Twig\Environment $environment, string $entityType, int $entityId, string $originPath, ?string $confirmMessage = null): string
    {
        $this->logger->debug('Creating delete form for entity type: ', ['entityType' => $entityType, 'entityId' => $entityId, 'originPath' => $originPath]);
        $options = [
            'entityType' => $entityType,
            'entityId' => $entityId,
            'originPath' => $originPath,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'delete-entity',
            'action' => $this->urlGenerator->generate('delete_entity'),
            'method' => 'POST',
            'attr' => ['class' => 'd-inline'],
        ];

        if ($confirmMessage) {
            $options['confirm_message'] = $confirmMessage;
        }

        $form = $this->formFactory->create(EntityDeletionType::class, null, $options);

        return $environment->render('services/entity_deletion_service/entity_deletion_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
