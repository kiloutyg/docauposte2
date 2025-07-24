<?php

namespace App\Form\Iluo;

use App\Entity\IluoLevels;
use App\Entity\StepsSubheadings;
use App\Entity\StepsTitle;
use App\Form\AbstractBaseFormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StepsSubheadingsType extends AbstractBaseFormType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $this->addTextField(
            builder: $builder,
            fieldName: 'heading',
            label: 'Sous-titre des étapes',
            placeholder: 'Sous-titre des étapes',
            required: true
        );
        $this->addEntityField(
            builder: $builder,
            fieldName: 'iluoLevel',
            label: 'Niveau ILUO correspondant',
            entityClass: IluoLevels::class,
            choiceLabel: 'level',
            placeholder: 'Sélectionner un niveau ILUO',
            required: true
        );
        $this->addEntityField(
            builder: $builder,
            fieldName: 'stepsTitle',
            label: 'Titre d\'Étape',
            entityClass: stepsTitle::class,
            choiceLabel: 'title',
            placeholder: 'Sélectionner un Titre d\'Étape',
            required: true
        );
        $this->addSubmitButton($builder);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => StepsSubheadings::class,
        ]);
    }
}
