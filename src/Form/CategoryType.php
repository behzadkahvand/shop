<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Media\CategoryImage;
use App\Form\Type\SlugType;
use Closure;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class CategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $category = isset($options['data']) ? $options['data'] : null;
        $builder
            ->add('code')
            ->add('title')
            ->add('pageTitle')
            ->add('subtitle')
            ->add('code', SlugType::class)
            ->add('level', NumberType::class)
            ->add('description')
            ->add('metaDescription')
            ->add('image', MediaType::class, ['data_class' => CategoryImage::class])
//            We disabled configurations field because it has `Json` data type and form can't
//              recognize what type of data user sent, so we fill it manually in controller.
//            ->add('configurations')
            ->add('parent', EntityType::class, [
                'class'       => Category::class,
                'constraints' => [
                    new Callback([
                        'callback' => $this->getParentValidator(),
                        'groups'   => $options['validation_groups'],
                    ]),
                ],
            ])
            ->add('categoryProductIdentifier', CategoryProductIdentifierType::class)
        ;

        if ($options['method'] == "PATCH" && $category?->isLeaf()) {
            $builder->add('commission', NumberType::class);
            $builder->add('maxLeadTime', NumberType::class);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'         => Category::class,
            'allow_extra_fields' => true,
        ]);
    }

    private function getParentValidator(): Closure
    {
        return function (?Category $payload, ExecutionContextInterface $context) {
            if (!$payload) {
                return;
            }

            if (!$payload->isLeaf()) {
                return;
            }

            if (!$payload->hasProducts()) {
                return;
            }

            $context->buildViolation('The category you selected is not valid parent.')
                    ->atPath('parent')
                    ->addViolation();
        };
    }
}
