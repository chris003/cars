<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TelType;

class ProfilModificationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName')
            ->add('lastName')
            ->add('email')
            ->add('phone', TelType::class)
            ->add('street')
            ->add('streetNum')
            ->add('postalCode')
            ->add('locality')
            ->add('country')
            ->add('password', HiddenType::class, [
                'data' => 'Az+1111111',
            ])
            ->add('confirm_password', HiddenType::class, [
                'data' => 'Az+1111111',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
