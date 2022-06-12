<?php

namespace App\Form;

use App\Entity\CouponGeneratorInstruction;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CouponGeneratorInstructionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('amount', IntegerType::class, [
                'label' => 'sylius.form.promotion_coupon_generator_instruction.amount',
            ])
            ->add('prefix', TextType::class, [
                'label' => 'sylius.form.promotion_coupon_generator_instruction.prefix',
                'required' => false,
                'empty_data' => '',
            ])
            ->add('codeLength', IntegerType::class, [
                'label' => 'sylius.form.promotion_coupon_generator_instruction.code_length',
            ])
            ->add('suffix', TextType::class, [
                'label' => 'sylius.form.promotion_coupon_generator_instruction.suffix',
                'required' => false,
                'empty_data' => '',
            ])
            ->add('expiresAt', DateType::class, [
                'required' => false,
                'label' => 'sylius.form.promotion_coupon_generator_instruction.expires_at',
                'widget' => 'single_text',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CouponGeneratorInstruction::class,
            'validation_groups' => [
                'coupon_generation_instruction.create',
                'coupon_generation_instruction.update',
            ]
        ]);
    }
}
