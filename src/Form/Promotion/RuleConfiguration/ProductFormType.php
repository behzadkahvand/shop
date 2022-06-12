<?php

namespace App\Form\Promotion\RuleConfiguration;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Service\Promotion\Rule\ProductRuleType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProductFormType extends AbstractType
{
    private ProductRepository $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(ProductRuleType::CONFIGURATION_PRODUCT_IDS, CollectionType::class, [
                'constraints' => [
                    new NotBlank(['groups' => 'promotion.create']),
                ],
                'entry_type' => EntityType::class,
                'entry_options' => [
                    'class' => Product::class,
                ],
                'allow_add' => true,
                'allow_delete' => false,
                'by_reference' => false,
                'error_bubbling' => false,
            ])
        ;

        $builder->get(ProductRuleType::CONFIGURATION_PRODUCT_IDS)->addViewTransformer(new CallbackTransformer(
            function ($collection) {
                if ($collection === null || empty($collection)) {
                    return $collection;
                }

                return $this->productRepository->findBy(['id' => $collection]);
            },
            function ($collection) {
                if ($collection === null) {
                    return null;
                }

                return array_map(fn($entity) => $entity->getId(), $collection);
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
