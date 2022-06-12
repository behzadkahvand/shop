<?php

namespace App\Form;

use App\Entity\City;
use App\Entity\CustomerAddress;
use App\Entity\OrderAddress;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmbeddedOrderAddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fullAddress')
            ->add('unit')
            ->add('floor')
            ->add('number')
            ->add('name')
            ->add('family')
            ->add('nationalCode')
            ->add('phone')
            ->add('postalCode')
            ->add('city', EntityType::class, [
                'class' => City::class,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => OrderAddress::class,
        ]);
    }
}
