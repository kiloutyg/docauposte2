<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\ShiftLeaders;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ShiftLeadersType extends AbstractType
{
    public function __construct()
    {
        //placeholder
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('user', EntityType::class, [
                'label' => 'Désignation Manager',
                'class' => User::class,
                'choice_label' => 'username',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.roles = :manager')
                        ->leftJoin('u.shiftLeader', 'sl')
                        ->andWhere('sl.id IS NULL')
                        ->setParameter('manager', '["ROLE_MANAGER"]')
                        ->orderBy('u.username', 'ASC');
                },
                'label_attr' => [
                    'class' => 'form-label fs-4',
                    'style' => 'color: #ffffff;'
                ],
                'placeholder' => 'Ajouter un Shift-Leader :',
                'attr' => [
                    'class' => 'form-control mx-auto mt-2',
                ],
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
            'data_class' => ShiftLeaders::class,
        ]);
    }
}
