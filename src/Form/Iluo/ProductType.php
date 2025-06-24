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
            builder: $builder,
            fieldName: 'name',
            label: 'Nom du Produit',
            placeholder: 'Nom du Produit',
            required: true,
            additionalOptions: [
                'attr' => [
                    'class' => 'form-control capitalize-all-letters',
                    'data-name-validation-target' => 'productName',
                    'data-action' => 'keyup->name-validation#validateProductName',
                ]
            ]
        );

        $this->addSubmitButton(
            builder: $builder,
            label: 'Ajouter',
            additionalOptions: [
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
