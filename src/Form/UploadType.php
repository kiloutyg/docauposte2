<?php

namespace App\Form;

use App\Entity\Upload;
use App\Entity\Button;

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
use App\Repository\ButtonRepository;


class UploadType extends AbstractType
{
    private $buttonRepository;
    private $logger;

    public function __construct(ButtonRepository $buttonRepository, LoggerInterface $logger)
    {
        $this->buttonRepository = $buttonRepository;
        $this->logger = $logger;
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('file', FileType::class, [
                'label' => 'Select a file to upload:',
                'mapped' => false,
                'required' => false,
            ])
            ->add('filename', TextType::class, [
                'label' => 'Nouveau nom du fichier:',
            ])
            // ;
            ->add('button', EntityType::class, [
                'class' => Button::class,
                'choice_label' => 'name',
                'label' => 'Select a button:',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Upload::class,
        ]);
    }
}