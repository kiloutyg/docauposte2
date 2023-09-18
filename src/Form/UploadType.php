<?php

namespace App\Form;

use Psr\Log\LoggerInterface;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use Doctrine\ORM\EntityRepository;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use App\Repository\ButtonRepository;


use App\Entity\Upload;
use App\Entity\Button;
use App\Entity\User;
use App\Entity\Validation;
use App\Entity\Approbation;



// This class is responsible for creating the form for the upload entity and transforming the data to be used by the controller and the entities.
// It also contains the logic for the form validation and the form submission.
class UploadType extends AbstractType
{
    // Dependency injection of ButtonRepository and LoggerInterface
    private $buttonRepository;
    private $logger;

    public function __construct(ButtonRepository $buttonRepository, LoggerInterface $logger)
    {
        $this->buttonRepository = $buttonRepository;
        $this->logger = $logger;
    }

    // Builds the form using FormBuilderInterface
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $currentUserId = $options['current_user_id'];
        $currentApprobationId = $options['current_approbation_id'];

        $builder
            // Adds a file input field to the form
            ->add(
                'file',
                FileType::class,
                [
                    'label' => 'Select a file to upload:',
                    'mapped' => true,
                    'required' => false,
                ]
            )
            // Adds a text input field for the filename to the form
            ->add(
                'filename',
                TextType::class,
                [
                    'label' => 'Nouveau nom du fichier:',
                    'required' => false,
                    'empty_data' => null,
                ]
            )
            // Adds an entity field for selecting a button to the form
            ->add(
                'button',
                EntityType::class,
                [
                    'class' => Button::class,
                    'choice_label' => 'name',
                    'label' => 'Select a button:',
                    'placeholder' => 'Choisir un bouton',
                    'required' => true,
                    'multiple' => false,
                ]
            )
            // // Adds an entity field for selecting approbators to the form
            // ->add(
            //     'approbator',
            //     EntityType::class,
            //     [
            //         'class' => User::class, // Adjust this to match your User entity namespace
            //         'query_builder' => function (EntityRepository $er) use ($currentUserId, $currentApprobationId) {
            //             return $er->createQueryBuilder('u')
            //                 ->leftJoin('u.approbations', 'a') // Assuming 'approbations' is a relationship in your User entity
            //                 ->where('u.roles NOT LIKE :role')
            //                 ->andWhere('a.id = :currentApprobationId')
            //                 ->andWhere('u.id != :currentUserId')
            //                 ->setParameter('role', '%ROLE_SUPER_ADMIN%')
            //                 ->setParameter('currentUserId', $currentUserId)
            //                 ->setParameter('currentApprobationId', $currentApprobationId)
            //                 ->orderBy('u.username', 'ASC');
            //         },
            //         'choice_label' => 'username', // Assuming your user entity has a 'username' property
            //         'label' => 'Select approbators:',
            //         'required' => false,
            //         'multiple' => true, // Now the form can accept multiple approbators
            //         'mapped' => false // This is the important part
            //     ]
            // )
            // // Adds a choice field for selecting the modification type to the form
            // ->add('modificationType', ChoiceType::class, [
            //     'mapped' => false,  // this tells Symfony not to expect the `modificationType` property on the entity
            //     'choices' => [
            //         'Modification Mineure' => 'light-modification',
            //         'Modification Majeure' => 'heavy-modification',
            //     ],
            // ])
        ;

        // Event listener triggered before form submission 
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();
            // If no filename was submitted, set it to the original filename
            if (empty($data['filename'])) {
                $data['filename'] = $form->getData()->getFilename();
                $event->setData($data);
            }
        });
    }

    // Configures the options for the form
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Upload::class,
            'current_user_id' => null,
            'current_upload_id' => null,
            'current_approbation_id' => null,
        ]);
    }
}