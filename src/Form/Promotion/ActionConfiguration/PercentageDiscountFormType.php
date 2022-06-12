<?php

namespace App\Form\Promotion\ActionConfiguration;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Type;

class PercentageDiscountFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('ratio', IntegerType::class, [
                'constraints' => [
                    new NotBlank(['groups' => 'promotion.create']),
                    new Type(['groups' => 'promotion.create', 'type' => 'int']),
                    new Range(['groups' => 'promotion.create', 'min' => 0, 'max' => 100]),
                ],
            ])
            ->add('max_amount', IntegerType::class, [
                'constraints' => [
                    new NotBlank(['groups' => 'promotion.create']),
                    new Type(['groups' => 'promotion.create', 'type' => 'int']),
                    new Range(['groups' => 'promotion.create', 'min' => 0]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
