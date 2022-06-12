<?php

namespace App\Form;

use App\Entity\ShippingPeriod;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ShippingPeriodType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('isActive');

        if ($options['method'] === 'POST') {
            $builder
                ->add('start', TimeType::class, [
                    'widget'       => 'single_text',
                    'input_format' => 'hh:mm',
                ])
                ->add('end', TimeType::class, [
                    'widget'       => 'single_text',
                    'input_format' => 'hh:mm',
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ShippingPeriod::class,
        ]);
    }
}
