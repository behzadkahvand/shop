<?php

namespace App\Form;

use App\Dictionary\ProductStatusDictionary;
use App\Entity\Customer;
use App\Entity\Product;
use App\Entity\Wishlist;
use Closure;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class WishlistType extends AbstractType
{
    private const ALLOWED_STATUSES = [
        ProductStatusDictionary::CONFIRMED,
        ProductStatusDictionary::UNAVAILABLE,
    ];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('customer', EntityType::class, ['class' => Customer::class])
            ->add('product', EntityType::class, [
                'class' => Product::class,
                'constraints' => [
                    new Callback([
                        'callback' => $this->getProductValidator(),
                        'groups' => $options['validation_groups'],
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Wishlist::class]);
    }

    private function getProductValidator(): Closure
    {
        return function (Product $payload, ExecutionContextInterface $context) {
            if (! in_array($payload->getStatus(), self::ALLOWED_STATUSES, true)) {
                $context
                    ->buildViolation('The selected product must have a allowed status!')
                    ->atPath('product')
                    ->addViolation();
            }
        };
    }

    private function isProductConfirmed(Product $payload): bool
    {
        return $payload->getStatus() === ProductStatusDictionary::CONFIRMED;
    }
}
