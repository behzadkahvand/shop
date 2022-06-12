<?php

namespace App\Form\Promotion;

use App\Entity\Customer;
use App\Entity\PromotionCoupon;
use App\Service\Promotion\DTO\PromotionCouponDTO;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class PromotionCouponType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code', TextType::class)
            ->add('expiresAt', DateType::class, [
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
            ])
            ->add('perCustomerUsageLimit', IntegerType::class)
            ->add('usageLimit', IntegerType::class)
            ->add('customers', CollectionType::class, [
                'entry_type' => EntityType::class,
                'entry_options' => [
                    'class' => Customer::class,
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'error_bubbling' => false,
            ])
            ->add('customersCsv', FileType::class, [
                'constraints' => [
                    new File([
                        'mimeTypes' => ['text/csv']
                    ])
                ],
                'mapped' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PromotionCouponDTO::class,
            'validation_groups' => ['promotionCoupon.create']
        ]);
    }
}
