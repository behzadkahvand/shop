<?php

namespace App\Form\Promotion\RuleConfiguration;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Service\Promotion\Rule\CategoryRuleType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validation;

class CategoryFormType extends AbstractType
{
    private CategoryRepository $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(CategoryRuleType::CONFIGURATION_CATEGORY_IDS, CollectionType::class, [
                'constraints' => [
                    new NotBlank(['groups' => 'promotion.create']),
                ],
                'entry_type' => EntityType::class,
                'entry_options' => [
                    'class' => Category::class,
                ],
                'allow_add' => true,
                'allow_delete' => false,
                'by_reference' => false,
                'error_bubbling' => false,
            ])
        ;

        $builder->get(CategoryRuleType::CONFIGURATION_CATEGORY_IDS)->addViewTransformer(new CallbackTransformer(
            function ($collection) {
                if ($collection === null || empty($collection)) {
                    return $collection;
                }

                return $this->categoryRepository->findBy(['id' => $collection]);
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
