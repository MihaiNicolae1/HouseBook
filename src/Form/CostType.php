<?php

namespace App\Form;

use App\Entity\Cost;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;


class CostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('description')
            ->add('value')
            ->add('currencyChoice', ChoiceType::class, array(
                'attr'  =>  array('class' => 'form-control',
                'style' => 'margin:5px 0;'),
                'choices' =>array(
                        'RON' => 'RON',
                        'EUR' => 'EUR',
                        'USD' => 'USD',
                ),
                'multiple' => false,
                'required' => true,
                'mapped' => false,
                )
            )
        ;
    }
}
