<?php

namespace App\Form\Promotion\RuleConfiguration;

use App\Service\Promotion\Rule\MinimumBasketTotalRuleType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class MinimumBasketTotalFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(MinimumBasketTotalRuleType::CONFIGURATION_BASKET_TOTAL, IntegerType::class, [
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
