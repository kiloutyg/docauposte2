<?php

namespace App\Form;

use App\Entity\Operators;
use App\Entity\Team;
use App\Entity\Uap;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\OptionsResolver\OptionsResolver;

class OperatorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('Team', EntityType::class, [
                'class' => Team::class,
'choice_label' => 'id',
            ])
            ->add('team', EntityType::class, [
                'class' => Team::class,
'choice_label' => 'id',
            ])
            ->add('uap', EntityType::class, [
                'class' => Uap::class,
'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Operators::class,
        ]);
    }
}