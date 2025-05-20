<?php

namespace App\Form;

use App\Entity\Steps;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StepsType extends AbstractBaseFormType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->addTextField(
            $builder,
            'question',
            'Question d\'Etape',
            'Question d\'Etape',
            true
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
