<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class PasswordModificationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('password', PasswordType::class)
            ->add('confirm_password', PasswordType::class)
            ->add('firstName', HiddenType::class)
            ->add('lastName', HiddenType::class)
            ->add('email', HiddenType::class)
            ->add('phone', HiddenType::class)
            ->add('street', HiddenType::class)
            ->add('streetNum', HiddenType::class)
            ->add('postalCode', HiddenType::class)
            ->add('locality', HiddenType::class)
            ->add('country', HiddenType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
