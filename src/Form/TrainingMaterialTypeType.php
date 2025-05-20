<?php

namespace App\Form;

use App\Entity\TrainingMaterialType;
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

        $this->addSubmitButton($builder);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TrainingMaterialType::class,
        ]);
    }
}
