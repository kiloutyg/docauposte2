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
            $builder,
            'name',
            'Type de Support de Formation',
            'Nom du Type de Support de Formation',
            true
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
