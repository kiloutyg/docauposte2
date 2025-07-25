<?php

namespace App\Form\Iluo;

use App\Entity\TrainingMaterialType;
use App\Entity\Upload;

use App\Form\AbstractBaseFormType;

use App\Model\TrainingMaterialTypeCategory;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TrainingMaterialTypeType extends AbstractBaseFormType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->addTextField(
            builder: $builder,
            fieldName: 'name',
            label: 'Type de Support de Formation',
            placeholder: 'Nom du Type de Support de Formation',
            required: true
        );
        $this->addEnumField(
            builder: $builder,
            fieldName: 'category',
            label: 'Catégorie du Type de Support de Formation (obligatoire)',
            enumClass: TrainingMaterialTypeCategory::class,
            placeholder: 'Sélectionner une Catégorie',
            required: true,
            additionalOptions: [
                'attr' => [
                    'data-training-material-type-form-target' => 'trainingMaterialTypeCateogorySelector',
                    'data-action' => 'change->training-material-type-form#trainingMaterialTypeCateogorySelectorChange',
                ]
            ]
        );
        $this->addEntityField(
            builder: $builder,
            fieldName: 'upload',
            label: 'Fichier spécifique à utiliser pour le Support de Formation',
            entityClass: Upload::class,
            choiceLabel: 'filename',
            placeholder: 'Sélectionner un Fichier',
            required: false,
            additionalOptions: [
                'attr' => [
                    'data-training-material-type-form-target' => 'uploadSelector',
                    'disabled' => true,

                ]
            ]
        );



        $this->addSubmitButton($builder);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TrainingMaterialType::class,
        ]);
    }
}
