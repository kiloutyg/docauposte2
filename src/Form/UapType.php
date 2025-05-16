<?php

namespace App\Form;

use App\Entity\Uap;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class UapType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de l\'Uap',
                'label_attr' => [
                    'style' => 'color: #ffffff;'
                ],
                'attr' => [
                    'class' => 'form-control capitalize-all-letters',
                    'placeholder' => 'Nom de l\'Uap',
                    'id' => 'name',
                    'required' => true,
                    'data-name-validation-target' => 'uapName',
                    'data-action' => 'input->name-validation#uapNameChanged',
                ]
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Ajouter',
                'attr' => [
                    'class' => 'btn btn-primary btn-login text-uppercase fw-bold mt-2 mb-3 submit-entity-creation',
                    'type' => 'submit'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Uap::class,
        ]);
    }
}
