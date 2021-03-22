<?php

namespace App\Form;

use App\Entity\Car;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Vich\UploaderBundle\Form\Type\VichFileType;

class CarType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('brand')
            ->add('model')
            ->add('doors', IntegerType::class, array('label' => false, 'attr' => array('min'=>'0', 'max' => '9')))
            ->add('transmission', ChoiceType::class, [
                'choices'  => [
                    'Manuelle' => Car::MANUAL_TRANSMISSION,
                    'Automatique' => Car::AUTOMATIC_TRANSMISSION,
                ]
            ])
            ->add('seats', IntegerType::class, array('label' => false, 'attr' => array('min'=>'0', 'max' => '99')))
            ->add('emission',IntegerType::class, array('label' => false, 'attr' => array('min'=>'0', 'max' => '999')))
            ->add('price', MoneyType::class, array('label' => false, 'attr' => array('pattern' => '[+]?([0-9]+(?:[\.][0-9]*)?|\.[0-9]+)$', 'title' => 'Veuillez entrer un nombre positif !')))
            ->add('imageFile', VichFileType::class, ['download_label' => 'Charger', 'attr' => [ 'accept' => '.jpg, .jpg, .png']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Car::class,
        ]);
    }
}
