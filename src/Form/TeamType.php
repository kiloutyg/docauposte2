<?php

namespace App\Form;

use App\Entity\Team;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use Symfony\Component\Validator\Constraints as Assert;

use Symfony\Component\Validator\Constraints\NotBlank;

class TeamType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de l\'équipe',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Nom de l\'équipe',
                    'id' => 'name',
                    'required' => true
                ],
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
            'data_class' => team::class,
        ]);
    }
}
