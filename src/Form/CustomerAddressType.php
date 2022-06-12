<?php

namespace App\Form;

use App\Entity\City;
use App\Entity\Customer;
use App\Entity\CustomerAddress;
use App\Entity\District;
use App\Entity\Province;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerAddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id')
            ->add('fullAddress')
            ->add('postalCode')
            ->add('isDefault')
            ->add('number', NumberType::class)
            ->add('name')
            ->add('family')
            ->add('nationalCode')
            ->add('mobile')
            ->add('city', EntityType::class, ['class' => City::class])
            ->add('district', EntityType::class, ['class' => District::class])
            ->add('province', EntityType::class, ['class' => Province::class])
            ->add('unit', NumberType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => CustomerAddress::class,
            'allow_extra_fields' => true,
        ]);
    }
}
