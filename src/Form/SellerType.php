<?php

namespace App\Form;

use App\Entity\Seller;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SellerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('username')
            ->add('nationalNumber')
            ->add('nationalIdentifier')
            ->add('password', TextType::class, ['property_path' => 'plainPassword'])
            ->add('isLimited')
            ->add('isRetail', CheckboxType::class, [
                'false_values' => [false, 'false', 0, '0', '', null],
            ])
            ->add('pickup')
            ->add('mobile')
            ->add('phone')
            ->add('address')
            ->add('fullName')
            ->add('shebaNumber')
            ->add('paymentPeriod')
            ->add('checkoutPeriod');

        if ($options['method'] === 'PATCH') {
            $builder->add('isActive');
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Seller::class,
        ]);
    }
}
