<?php

namespace App\Form\Iluo;

use App\Entity\IluoLevels;
use App\Form\AbstractBaseFormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IluoLevelsType extends AbstractBaseFormType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->addTextField(
            builder: $builder,
            fieldName: 'level',
            label: 'Appélation du Niveau (ILUO)',
            placeholder: 'Appélation du Niveau (ILUO)',
            required: true
        );
        $this->addTextField(
            builder: $builder,
            fieldName: 'description',
            label: 'Description du Niveau',
            placeholder: 'Description du Niveau',
            required: true
        );
        $this->addSubmitButton($builder);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => IluoLevels::class,
        ]);
    }
}
