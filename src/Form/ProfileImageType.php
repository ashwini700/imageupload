<?php

namespace App\Form;

use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use App\Entity\UserProfile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class ProfileImageType extends AbstractType
{


    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('profileImage', FileType::class, [
                'label' => 'Profile image (JPG or PNG file)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    // new Image([
                    //     // 'minWidth'=> '200',
                    //     // 'maxWidth'=> '400',
                    //     // 'minHeight'=>'200',
                    //     // 'maxHeight'=>'400',
                        
                    // ]),
                    new File([
                        'maxSize' => '2550k',              
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png'
                        ],
                        
                        'mimeTypesMessage' => 'Please upload a valid PNG/JPEG image'
                    ])
                    
                ]
            ]);
          
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }

   
}
