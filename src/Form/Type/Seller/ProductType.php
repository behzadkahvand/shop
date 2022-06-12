<?php

namespace App\Form\Type\Seller;

use App\Dictionary\ConfigurationCodeDictionary;
use App\Entity\Brand;
use App\Entity\Category;
use App\Entity\Media\ProductFeaturedImage;
use App\Entity\Media\ProductGallery;
use App\Entity\Product;
use App\Form\MediaType;
use App\Form\ProductIdentifierType;
use App\Service\Configuration\ConfigurationServiceInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ProductType extends AbstractType
{
    protected ConfigurationServiceInterface $configurationService;

    public function __construct(ConfigurationServiceInterface $configurationService)
    {
        $this->configurationService = $configurationService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('subtitle')
            ->add('description')
            ->add('weight')
            ->add('height')
            ->add('width')
            ->add('length')
            ->add('link', null, [
                'constraints' => new Url(['groups' => ['seller.product.create']]),
            ])
            ->add('images', CollectionType::class, [
                'entry_type'    => MediaType::class,
                'entry_options' => ['data_class' => ProductGallery::class],
                'allow_add'     => true,
                'allow_delete'  => true,
                'by_reference'  => false,
            ])
            ->add('featuredImage', MediaType::class, ['data_class' => ProductFeaturedImage::class])
            ->add('isOriginal')
            ->add('brand', EntityType::class, ['class' => Brand::class])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'constraints' => [
                    new Callback([
                        new NotBlank(),
                        'callback' => function (Category $category, ExecutionContextInterface $context, $payload) {
                            if (in_array($category->getId(), $this->getExcludedCategories(), true)) {
                                $msg = 'You can not create product with invalid category';

                                $context->buildViolation($msg)->addViolation();
                            }
                        },
                        'groups' => $options['validation_groups'],
                    ]),
                ]
            ])
            ->add('productIdentifiers', CollectionType::class, [
                'entry_type'   => ProductIdentifierType::class,
                'allow_add'    => true,
                'allow_delete' => true,
                'by_reference' => false,
                'delete_empty' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
            'allow_extra_fields' => true
        ]);
    }

    /**
     * @return array<integer>
     */
    protected function getExcludedCategories(): array
    {
        $config = $this->configurationService->findByCode(
            ConfigurationCodeDictionary::SELLER_SEARCH_EXCLUDED_CATEGORIES
        );

        if ($config === null || $config->getValue() === null) {
            return [];
        }

        return array_map(fn($v) => (int)$v, (array)$config->getValue());
    }
}
