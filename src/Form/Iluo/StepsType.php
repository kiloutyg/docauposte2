<?php

namespace App\Form\Iluo;

use App\Entity\Steps;
use App\Entity\IluoLevels;
use App\Entity\StepsSubheadings;
use App\Entity\StepsTitle;
use App\Entity\TrainingMaterialType;

use App\Form\AbstractBaseFormType;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StepsType extends AbstractBaseFormType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->addTextField(
            builder: $builder,
            fieldName: 'question',
            label: 'Question d\'Etape',
            placeholder: 'Question d\'Etape',
            required: true,
        );
        $this->addEntityField(
            builder: $builder,
            fieldName: 'iluoLevel',
            label: 'Niveau ILUO',
            entityClass: IluoLevels::class,
            choiceLabel: 'level',
            placeholder: 'Sélectionner un niveau ILUO',
            required: true
        );
        $this->addEntityField(
            builder: $builder,
            fieldName: 'stepsTitle',
            label: 'Titre d\'Étape',
            entityClass: StepsTitle::class,
            choiceLabel: 'title',
            placeholder: 'Sélectionner un Titre d\'Étape',
            required: false
        );
        $this->addEntityField(
            builder: $builder,
            fieldName: 'stepsSubheadings',
            label: 'Sous-Titre d\'Étape',
            entityClass: StepsSubheadings::class,
            choiceLabel: 'heading',
            placeholder: 'Sélectionner un Sous-Titre d\'Étape',
            required: false
        );
        $this->addEntityField(
            builder: $builder,
            fieldName: 'trainingMaterialType',
            label: 'Type de Matériel d\'Apprentissage',
            entityClass: TrainingMaterialType::class,
            choiceLabel: 'name',
            placeholder: 'Sélectionner un Type de Matériel d\'Apprentissage',
            required: false,
            additionalOptions: ['multiple' => true]
        );

        $this->addSubmitButton($builder);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Steps::class,
        ]);
    }
}
