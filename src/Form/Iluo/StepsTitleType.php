<?php

namespace App\Form\Iluo;

use App\Entity\IluoLevels;
use App\Entity\StepsTitle;
use App\Form\AbstractBaseFormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StepsTitleType extends AbstractBaseFormType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->addTextField(
            builder: $builder,
            fieldName: 'title',
            label: 'Titre de l\'Étape',
            placeholder: 'Titre de l\'Étape',
            required: true
        );

        $this->addEntityField(
            builder: $builder,
            fieldName: 'iluoLevel',
            label: 'Niveau ILUO',
            entityClass: IluoLevels::class,
            choiceLabel: 'id',
            placeholder: 'Sélectionner un niveau ILUO',
            required: true
        );
        
        $this->addSubmitButton($builder);
        }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => StepsTitle::class,
        ]);
    }
}
