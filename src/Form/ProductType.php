<?php

namespace App\Form;

use App\Entity\products;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    public function __construct()
    {
        //placeholder
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du Produit',
                'label_attr' => [
                    'class' => 'form-label fs-4',
                    'style' => 'color: #ffffff;'
                ],
                'attr' => [
                    'class' => 'form-control capitalize-all-letters',
                    'placeholder' => 'Nom du Produit',
                    'id' => 'name',
                    'required' => true,
                    'data-name-validation-target' => 'productName',
                    'data-action' => 'keyup->name-validation#validateProductName',
                ]
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Ajouter',
                'attr' => [
                    'class' => 'btn btn-primary btn-login text-uppercase fw-bold mt-2 mb-3 submit-entity-creation',
                    'type' => 'submit',
                    'data-name-validation-target' => 'saveButton',
                ]
            ]);
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Products::class,
        ]);
    }
}
