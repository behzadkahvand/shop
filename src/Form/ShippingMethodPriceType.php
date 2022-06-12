<?php

namespace App\Form;

use App\Entity\ShippingMethod;
use App\Entity\ShippingMethodPrice;
use App\Entity\Zone;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ShippingMethodPriceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('price', NumberType::class)
            ->add('zone', EntityType::class, ['class' => Zone::class])
            ->add('shippingMethod', EntityType::class, ['class' => ShippingMethod::class]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ShippingMethodPrice::class,
        ]);
    }
}
