<?php

namespace App\Form;

use App\Entity\Apology;
use App\Entity\Promotion;
use App\Repository\PromotionRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ApologyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('codePrefix', TextType::class, [
                'constraints' => [
                    new NotBlank(['groups' => 'apology.create']),
                ]
            ])
            ->add('messageTemplate', TextType::class, [
                'constraints' => [
                    new NotBlank(['groups' => 'apology.create']),
                ]
            ])
            ->add('promotion', EntityType::class, [
                'class' => Promotion::class,
                'query_builder' => function (PromotionRepository $repository) {
                    return $repository->couponBasedQueryBuilder();
                },
                'constraints' => [
                    new NotBlank(['groups' => 'apology.create']),
                ],
            ])
        ;
    }
}
