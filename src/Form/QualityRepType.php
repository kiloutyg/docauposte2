<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\QualityRep;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QualityRepType extends AbstractType
{
    public function __construct()
    {
        //placeholder
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('user', EntityType::class, [
                'label' => 'Désignation QualityRep',
                'class' => User::class,
                'choice_label' => 'username',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.roles = :admin or u.roles = :lineAdmin')
                        ->leftJoin('u.qualityRep', 'qr')
                        ->andWhere('qr.id IS NULL')
                        ->setParameter('admin', '["ROLE_ADMIN"]')
                        ->setParameter('lineAdmin', '["ROLE_LINE_ADMIN"]')
                        ->orderBy('u.username', 'ASC');
                },
                'label_attr' => [
                    'class' => 'form-label fs-4',
                    'style' => 'color: #ffffff;'
                ],
                'placeholder' => 'Ajouter un QualityRep :',
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
            'data_class' => QualityRep::class,
        ]);
    }
}
