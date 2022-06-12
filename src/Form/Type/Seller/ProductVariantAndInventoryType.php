<?php

namespace App\Form\Type\Seller;

use App\Dictionary\ProductStatusDictionary;
use App\DTO\Admin\ProductVariantAndInventoryData;
use App\Entity\Product;
use App\Entity\ProductOptionValue;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\PositiveOrZero;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ProductVariantAndInventoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('product', EntityType::class, [
                'class'       => Product::class,
                'constraints' => [
                    new NotBlank(),
                    new Callback(function (Product $product, ExecutionContextInterface $context, $payload) {
                        $allowedStatuses = [ProductStatusDictionary::CONFIRMED, ProductStatusDictionary::UNAVAILABLE];

                        if (!in_array($product->getStatus(), $allowedStatuses, true)) {
                            $context->buildViolation('Chosen product should be in confirmed or unavailable.')
                                    ->addViolation();
                        }
                    }),
                    new Callback(function (Product $product, ExecutionContextInterface $context, $payload) {
                        if (!$product->productIdentifierConstraintIsResolved()) {
                            $context->buildViolation('Chosen product has no product identifier!')
                                    ->atPath('product')
                                    ->addViolation();
                        }
                    }),
                ]
            ])
            ->add('optionValues', EntityType::class, [
                'multiple'    => true,
                'class'       => ProductOptionValue::class,
                'constraints' => [
                    new Count([
                        'min'        => 1,
                        'minMessage' => "This value should not be blank."
                    ])
                ]
            ])
            ->add('stock', null, [
                'constraints' => [
                    new NotBlank(),
                    new NotNull(),
                    new PositiveOrZero(),
                ]
            ])
            ->add('price', null, [
                'constraints' => [
                    new NotBlank(),
                    new NotNull(),
                    new PositiveOrZero(),
                    new GreaterThanOrEqual([
                        'propertyPath' => 'parent.all[finalPrice].data'
                    ]),
                ]
            ])
            ->add('finalPrice', null, [
                'constraints' => [
                    new NotBlank(),
                    new NotNull(),
                    new PositiveOrZero(),
                ]
            ])
            ->add('maxPurchasePerOrder', null, [
                'constraints' => [
                    new NotBlank(),
                    new NotNull(),
                    new PositiveOrZero(),
                ]
            ])
            ->add('suppliesIn', null, [
                'constraints' => [
                    new NotBlank(),
                    new NotNull(),
                    new PositiveOrZero(),
                ]
            ])
            ->add('sellerCode')
            ->add('isActive', CheckboxType::class, [
                'false_values' => [false, 'false', 0, '0', '', null]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => ProductVariantAndInventoryData::class,
            'allow_extra_fields' => true,
        ]);
    }
}
