<?php

namespace App\Form\Promotion;

use App\Entity\Promotion;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\NotBlank;

class PromotionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class)
            ->add('enabled', ChoiceType::class, [
                'empty_data' => 0,
                'choices' => [
                    'Yes' => 1,
                    'No' => 0
                ]
            ])
            ->add('description', TextType::class)
            ->add('priority', NumberType::class)
            ->add('usageLimit')
            ->add('couponBased', null, [
                'constraints' => new Choice([true, false])
            ])
            ->add('startsAt', DateTimeType::class, [
                'widget' => 'single_text',
            ])
            ->add('endsAt', DateTimeType::class, [
                'widget' => 'single_text',
            ])
            ->add('rules', CollectionType::class, [
                'entry_type' => PromotionRuleType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'error_bubbling' => false,
            ])
            ->add('actions', CollectionType::class, [
                'entry_type' => PromotionActionType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'error_bubbling' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Promotion::class,
            'validation_groups' => 'promotion.create'
        ]);
    }
}
