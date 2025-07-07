<?php

namespace App\Form;

use App\Repository\ProductLineRepository;

use App\Entity\Incident;
use App\Entity\ProductLine;
use App\Entity\IncidentCategory;

use Psr\Log\LoggerInterface;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

use Symfony\Component\OptionsResolver\OptionsResolver;

// This class is responsible for creating the form for the incident entity and transforming the data to be used by the controller and the entit. 
// It also contains the logic for the form validation and the form submission.
class IncidentType extends AbstractType
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'file',
                FileType::class,
                [
                    'label' => 'Select a file to upload:',
                    'mapped' => true,
                    'required' => false,
                ]
            )
            ->add(
                'name',
                TextType::class,
                [
                    'label' => 'Nouveau nom du fichier d\'incident:',
                    'required' => false,
                    'empty_data' => null,
                    'attr' => [
                        'data-filename-validation-target' => 'filename',
                        'data-action' => 'change->filename-validation#validateFilename'
                    ]
                ]
            )
            ->add(
                'productLine',
                EntityType::class,
                [
                    'class' => ProductLine::class,
                    'choice_label' => 'name',
                    'label' => 'Select a productLine:',
                    'placeholder' => 'Choisir un Produit',
                    'required' => true,
                    'multiple' => false,
                ]
            )
            ->add(
                'incidentCategory',
                EntityType::class,
                [
                    'class' => IncidentCategory::class,
                    'choice_label' => 'name',
                    'label' => 'Select an incident category:',
                    'placeholder' => 'Choisir un type d\'incident',
                    'required' => true,
                    'multiple' => false,
                ]
            )
            ->add(
                'autoDisplayPriority',
                ChoiceType::class,
                [
                    'required' => true,
                    'choices' => array_combine(range(0, 5), range(0, 5)),
                    'label' => 'Select a priority level:',
                    'placeholder' => 'Sélectionner le niveau de priorité',
                ]
            );

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();
            // If no filename was submitted, set it to the original filename
            if (empty($data['name'])) {
                $data['name'] = $form->getData()->getName();
                $event->setData($data);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Incident::class,
        ]);
    }
}
