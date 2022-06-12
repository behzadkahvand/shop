<?php

namespace App\Form\Promotion\RuleConfiguration;

use App\Service\Promotion\Rule\MaximumOrdersCountRuleType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class MaximumOrdersCountFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(MaximumOrdersCountRuleType::CONFIGURATION_ORDERS_COUNT, IntegerType::class, [
                'constraints' => [
                    new NotBlank(['groups' => 'promotion.create']),
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
