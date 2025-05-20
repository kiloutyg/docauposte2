<?php

namespace App\Form;

use App\Entity\Products;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractBaseFormType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->addTextField(
            $builder,
            'name',
            'Nom du Produit',
            'Nom du Produit',
            true,
            [
                'attr' => [
                    'class' => 'form-control capitalize-all-letters',
                    'data-name-validation-target' => 'productName',
                    'data-action' => 'keyup->name-validation#validateProductName',
                ]
            ]
        );

        $this->addSubmitButton(
            $builder,
            'Ajouter',
            [
                'attr' => [
                    'data-name-validation-target' => 'saveButton',
                ]
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Products::class,
        ]);
    }
}
