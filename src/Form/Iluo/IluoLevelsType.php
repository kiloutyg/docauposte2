<?php

namespace App\Form\Iluo;

use App\Entity\IluoLevels;
use App\Form\AbstractBaseFormType;

use App\Service\Facade\EntityManagerFacade;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

use Symfony\Component\OptionsResolver\OptionsResolver;


class IluoLevelsType extends AbstractBaseFormType
{
    private $entityManagerFacade;
    private $iluoLevelsCount;

    public function __construct(EntityManagerFacade $entityManagerFacade)
    {
        $this->entityManagerFacade = $entityManagerFacade;
        $this->iluoLevelsCount = $this->entityManagerFacade->count(entityType: 'iluoLevels', criteria: []);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->addTextField(
            builder: $builder,
            fieldName: 'level',
            label: 'Appélation du Niveau (ILUO)',
            placeholder: 'Appélation du Niveau (ILUO)',
            required: true
        );
        $this->addTextField(
            builder: $builder,
            fieldName: 'description',
            label: 'Description du Niveau',
            placeholder: 'Description du Niveau',
            required: true
        );
        $builder->add(
            'priorityOrder',
            ChoiceType::class,
            [
                'label' => 'Ordre de priorité',
                'label_attr' => [
                    'class' => self::FORM_LABEL_CLASSES,
                    'style' => self::LABEL_STYLE
                ],
                'choices' => array_combine(keys: range(start: 1, end: $this->iluoLevelsCount + 1), values: range(start: 1, end: $this->iluoLevelsCount + 1)),
                'required' => true,
                'attr' => [
                    'class' => self::FORM_CONTROL_CLASSES,
                    'placeholder' => 'Ordre de priorité',
                ],
            ]
        );
        $builder->add(
            'qualityRepNeeded',
            ChoiceType::class,
            [
                'label' => 'Validation par Animateur Qualité requise?',
                'label_attr' => [
                    'class' => self::FORM_LABEL_CLASSES,
                    'style' => self::LABEL_STYLE
                ],
                'choices' => [
                    'Non' => false,
                    'Oui' => true,

                ],
                'required' => true,
                'attr' => [
                    'class' => self::FORM_CONTROL_CLASSES,
                    'placeholder' => 'Validation par Animateur Qualité requise?',
                ],
            ]
        );

        $this->addSubmitButton($builder);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => IluoLevels::class,
        ]);
    }
}
