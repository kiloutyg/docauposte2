<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntityDeletionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('entityType', HiddenType::class, [
                'data' => $options['entityType'],
            ])
            ->add('entityId', HiddenType::class, [
                'data' => $options['entityId'],
            ])
            ->add('originPath', HiddenType::class, [
                'data' => $options['originPath'],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Supprimer',
                'attr' => [
                    'aria-label' => 'Supprimer l\'élément',
                    'class' => 'btn btn-danger delete-workstation shadow tooltips-above',
                ],
                'label_html' => true,
                'label_format' => '<span class="d-none d-md-inline">%name%</span><i class="fas fa-trash d-inline d-md-none" aria-hidden="true" title="Supprimer"></i><span class="visually-hidden">Supprimer</span>',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {

        $resolver->setDefaults([
            'entityType' => null,
            'entityId' => null,
            'originPath' => 'app_base',
            'confirm_message' => 'Êtes-vous sûr de vouloir supprimer cet élément ?',
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'delete-entity',
        ]);

        $resolver->setRequired(['entityType', 'entityId', 'originPath']);
        $resolver->setAllowedTypes('entityType', 'string');
        $resolver->setAllowedTypes('entityId', 'int');
        $resolver->setAllowedTypes('originPath', 'string');
        $resolver->setAllowedTypes('confirm_message', 'string');
    }

    public function getBlockPrefix(): string
    {
        return 'delete_entity';
    }
}
