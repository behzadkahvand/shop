<?php

namespace App\Form;

use App\Entity\ShippingCategory;
use App\Entity\ShippingMethod;
use App\Entity\ShippingPeriod;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ShippingMethodType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('categories', EntityType::class, [
                "class" => ShippingCategory::class,
                "multiple" => true,
                'by_reference' => false
            ])
            ->add(
                'shippingMethodPrices',
                CollectionType::class,
                ['entry_type' => ShippingMethodPriceType::class,
                    'allow_add' => true, 'allow_delete' => true,
                    'by_reference' => false
                ]
            )
            ->add('periods', EntityType::class, [
                "class" => ShippingPeriod::class,
                "multiple" => true,
                'by_reference' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ShippingMethod::class,
            'allow_extra_fields' => true
        ]);
    }
}
