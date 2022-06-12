<?php

namespace App\Form\Type;

use App\Dictionary\ProductStatusDictionary;
use App\Entity\Brand;
use App\Entity\Category;
use App\Entity\Media\ProductFeaturedImage;
use App\Entity\Media\ProductGallery;
use App\Entity\Product;
use App\Entity\ProductOption;
use App\Entity\Seller;
use App\Entity\ShippingCategory;
use App\Form\MediaType;
use App\Form\ProductIdentifierType;
use App\Service\Product\Availability\ProductAvailabilityChecker;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ProductType extends AbstractType
{
    /**
     * @var ProductAvailabilityChecker
     */
    private ProductAvailabilityChecker $availabilityChecker;

    /**
     * ProductType constructor.
     *
     * @param ProductAvailabilityChecker $availabilityChecker
     */
    public function __construct(ProductAvailabilityChecker $availabilityChecker)
    {
        $this->availabilityChecker = $availabilityChecker;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('subtitle')
            ->add('alternativeTitle')
            ->add('description')
            ->add('isActive')
            ->add('weight')
            ->add('height')
            ->add('width')
            ->add('length')
            ->add('metaDescription')
            ->add('additionalTitle')
            ->add('link', null, [
                'constraints' => new Url(['groups' => ['create']]),
            ])
            ->add('EAV')
            ->add(
                'summaryEAV',
                CollectionType::class,
                [
                    'prototype'    => true,
                    'allow_add'    => true,
                    'allow_delete' => true,
                    'by_reference' => false
                ]
            )
            ->add('images', CollectionType::class, [
                'entry_type' => MediaType::class,
                'entry_options' => ['data_class' => ProductGallery::class],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ])
            ->add('featuredImage', MediaType::class, ['data_class' => ProductFeaturedImage::class])
            ->add('status', ChoiceType::class, [
                'choices'     => array_values(ProductStatusDictionary::toArray()),
                'constraints' => [
                    new Callback([
                        'callback' => function ($newStatus, ExecutionContextInterface $context, $payload) {
                            $statuses = [ProductStatusDictionary::CONFIRMED, ProductStatusDictionary::UNAVAILABLE];

                            if (!in_array($newStatus, $statuses, true)) {
                                return;
                            }

                            /** @var Product $product */
                            $product     = $context->getRoot()->getData();
                            $isAvailable = $this->availabilityChecker->isAvailable($product);

                            if ($isAvailable) {
                                if ($this->availabilityChecker->shouldBeUnavailable($product)) {
                                    $msg = 'Product has no active inventory hence it can not be confirmed';
                                    $context->buildViolation($msg)->addViolation();
                                }

                                return;
                            }

                            if ($this->availabilityChecker->shouldBeAvailable($product)) {
                                $msg = 'Product has active inventory hence it can not be unavailable';
                                $context->buildViolation($msg)->addViolation();
                            }
                        },
                        'groups' => $options['validation_groups'],
                    ]),
                ],
            ])
            ->add('isOriginal')
            ->add('brand', EntityType::class, ['class' => Brand::class])
            ->add('category', EntityType::class, ['class' => Category::class])
            ->add('shippingCategory', EntityType::class, ['class' => ShippingCategory::class])
            ->add('options', EntityType::class, ['class' => ProductOption::class, 'multiple' => true])
            ->add('productIdentifiers', CollectionType::class, [
                'entry_type'    => ProductIdentifierType::class,
                'entry_options' => [
                    'product' => $options['data'] ?? null,
                ],
                'allow_add'     => true,
                'allow_delete'  => true,
                'by_reference'  => false,
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();

            if (isset($data['seller'])) {
                $event->getForm()->add('seller', EntityType::class, ['class' => Seller::class]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
            'allow_extra_fields' => true,
        ]);
    }
}
