<?php

namespace App\Form;

use App\Dictionary\SellerFormCommodityDiversityDictionary;
use App\Dictionary\SellerFormOwnershipTypeDictionary;
use App\Entity\Category;
use App\Entity\MarketingSellerLanding;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SellerFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('email')
            ->add('company')
            ->add('phone')
            ->add('categories', EntityType::class, [
                'class'        => Category::class,
                'multiple'     => true,
                'by_reference' => false,
            ])
            ->add('commodityDiversity', ChoiceType::class, [
                'choices' => array_values(SellerFormCommodityDiversityDictionary::toArray()),
            ])
            ->add('ownershipType', ChoiceType::class, [
                'choices' => array_values(SellerFormOwnershipTypeDictionary::toArray()),
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'         => MarketingSellerLanding::class,
                'allow_extra_fields' => true,
            ]
        );
    }
}
