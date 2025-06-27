<?php

namespace App\Form\Iluo;

use App\Entity\Operator;
use App\Entity\User;
use App\Entity\ShiftLeaders;
use App\Form\AbstractBaseFormType;

use Symfony\Component\Form\FormBuilderInterface;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;


use Symfony\Component\OptionsResolver\OptionsResolver;

class ShiftLeadersType extends AbstractBaseFormType
{
    public function __construct()
    {
        //placeholder
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->addEntityField(
            builder: $builder,
            fieldName: 'user',
            label: 'Désignation Manager(Shift-Leader)',
            entityClass: User::class,
            choiceLabel: 'username',
            queryBuilder: function (EntityRepository $er) {
                return $er->createQueryBuilder('u')
                    ->select('u')
                    ->where('u.roles LIKE :role')
                    ->leftJoin(ShiftLeaders::class, 'sl', Join::WITH, 'sl.user = u')
                    ->andWhere('sl.id IS NULL')
                    ->setParameter('role', '%"ROLE_MANAGER"%')
                    ->orderBy('u.username', 'ASC');
            },
            placeholder: 'Ajouter depuis un Utilisateur :',
            required: true,
            additionalOptions: [
                'attr' => [
                    'data-shift-leaders-form-target' => 'userShiftLeaders',
                    'data-action' => 'change->shift-leaders-form#userShiftLeadersChange',
                ]
            ]
        );

        $this->addEntityField(
            builder: $builder,
            fieldName: 'operator',
            label: 'Désignation Manager',
            entityClass: Operator::class,
            choiceLabel: 'name',
            queryBuilder: function (EntityRepository $er) {
                return $er->createQueryBuilder('o')
                    ->select('o')
                    ->where('o.isTrainer = :isTrainer')
                    ->leftJoin(ShiftLeaders::class, 'sl', Join::WITH, 'sl.operator = o')
                    ->andWhere('sl.id IS NULL')
                    ->setParameter('isTrainer', true)
                    ->orderBy('o.name', 'ASC');
            },
            placeholder: 'Ajouter depuis un Operateur Formateur :',
            required: true,
            additionalOptions: [
                'attr' => [
                    'data-shift-leaders-form-target' => 'operatorShiftLeaders',
                    'data-action' => 'change->shift-leaders-form#operatorShiftLeadersChange',
                ]
            ]
        );

        $this->addSubmitButton(
            builder: $builder,
            additionalOptions: [
                'attr' => ['data-name-validation-target' => 'saveButton'],
            ]
        );
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ShiftLeaders::class,
        ]);
    }
}
