<?php

namespace App\Form;

use App\Dictionary\ProductStatusDictionary;
use App\Entity\Customer;
use App\Entity\Product;
use App\Entity\ProductNotifyRequest;
use Closure;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class NotifyMeType extends AbstractType
{
    private const ALLOWED_STATUSES = [
        ProductStatusDictionary::UNAVAILABLE,
    ];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('customer', EntityType::class, ['class' => Customer::class])
            ->add(
                'product',
                EntityType::class,
                [
                    'class'       => Product::class,
                    'constraints' => [
                        new Callback(
                            [
                                'callback' => $this->getProductValidator(),
                            ]
                        ),
                    ],
                ]
            );
    }

    private function getProductValidator(): Closure
    {
        return function (Product $payload, ExecutionContextInterface $context) {
            if (!in_array($payload->getStatus(), self::ALLOWED_STATUSES, true)) {
                $context
                    ->buildViolation('The selected product must have a allowed status!')
                    ->atPath('product')
                    ->addViolation();
            }
        };
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => ProductNotifyRequest::class]);
    }
}
