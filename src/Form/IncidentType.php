<?php

namespace App\Form;

use App\Repository\ProductLineRepository;

use App\Entity\Incident;
use App\Entity\ProductLine;

use Psr\Log\LoggerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\StringerType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

// This class is responsible for creating the form for the incident entity and transforming the data to be used by the controller and the entit. 
// It also contains the logic for the form validation and the form submission.
class IncidentType extends AbstractType
{
    private $productLineRepository;
    private $logger;

    public function __construct(ProductLineRepository $productLineRepository, LoggerInterface $logger)
    {
        $this->productLineRepository = $productLineRepository;
        $this->logger = $logger;
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('file', FileType::class, [
                'label' => 'Select a file to upload:',
                'mapped' => true,
                'required' => false,
            ])
            ->add('name', TextType::class, [
                'label' => 'Nouveau nom du fichier d\'incident:',
                'required' => false,
                'empty_data' => null,
            ])
            ->add(
                'productline',
                EntityType::class,
                [
                    'class' => ProductLine::class,
                    'choice_label' => 'name',
                    'label' => 'Select a productline:',
                    'placeholder' => 'Choisir un Produit',
                    'required' => true,
                    'multiple' => false,
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