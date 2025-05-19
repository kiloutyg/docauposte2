<?php

namespace App\Form;

use App\Entity\Operator;
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
                'label' => 'Désignation Manager(Shift-Leader)',
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
                'placeholder' => 'Ajouter depuis un Utilisateur :',
                'attr' => [
                    'class' => 'form-control mx-auto mt-2',
                    'data-shift-leaders-form-target' => 'userShiftLeaders',
                    'data-action' => 'change->shift-leaders-form#userShiftLeadersChange',
                ],
            ])
            ->add('operator', EntityType::class, [
                'label' => 'Désignation Manager',
                'class' => Operator::class,
                'choice_label' => 'name',
                'query_builder' => fn(EntityRepository $er) => $er->createQueryBuilder('o')
                    ->where('o.isTrainer = :true')
                    ->leftJoin('o.shiftLeaders', 'sl')
                    ->andWhere('sl.id IS NULL')
                    ->setParameter('true', 'true')
                    ->orderBy('o.name', 'ASC'),
                'label_attr' => [
                    'class' => 'form-label fs-4',
                    'style' => 'color: #ffffff;'
                ],
                'placeholder' => 'Ajouter depuis un Operateur Formateur :',
                'attr' => [
                    'class' => 'form-control mx-auto mt-2',
                    'data-shift-leaders-form-target' => 'operatorShiftLeaders',
                    'data-action' => 'change->shift-leaders-form#operatorShiftLeadersChange',
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
