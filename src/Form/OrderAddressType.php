<?php

namespace App\Form;

use App\DTO\OrderAddressData;
use App\Entity\CustomerAddress;
use App\Entity\Order;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderAddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('order', EntityType::class, [
                'class' => Order::class,
            ])
            ->add('address', EntityType::class, [
                'class' => CustomerAddress::class,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => OrderAddressData::class,
        ]);
    }
}
