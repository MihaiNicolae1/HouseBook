<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\File;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email',EmailType::class)
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('FirstName', TextType::class, [
               
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a First Name',
                    ]),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'Your First Name should be at least {{ limit }} characters',
                        'max' => 255,
                    ]),
                ],
            ])
            ->add('LastName', TextType::class, [
               
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a First Name',
                    ]),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'Your Last Name should be at least {{ limit }} characters',
                        'max' => 255,
                    ]),
                ],
            ])
            ->add('roles', ChoiceType::class, array(
                'attr'  =>  array('class' => 'form-control',
                'style' => 'margin:5px 0;'),
                'choices' =>array(
                        'Beneficiary' => 'ROLE_BENEFICIARY',
                        'Architect' => 'ROLE_ARCHITECT',
                        'Accountant' => 'ROLE_ACCOUNTANT',
                        'Worker' => 'ROLE_WORKER',
                ),
                'multiple' => true,
                'required' => true,
                )
            )
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'You should agree to our terms.',
                    ]),
                ],
            ])
            ->add('ProfilePicture', FileType::class,[
                'label' => 'Profile picture(JPG/JPEG)',

                // unmapped means that this field is not associated to any entity property
                'mapped' => false,

                // make it optional so you don't have to re-upload the PDF file
                // every time you edit the Product details
                'required' => false,

                // unmapped fields can't define their validation using annotations
                // in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new File([
                        'maxSize' => '10240k',
                        
                        'mimeTypesMessage' => 'Please upload a valid JPG or JPEG document',
                    ])
                ],
            ]);

}

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
