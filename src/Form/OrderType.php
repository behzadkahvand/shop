<?php

namespace App\Form;

use App\Entity\Order;
use App\Entity\PromotionCoupon;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class OrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('isLegal', CheckboxType::class, [
                'false_values' => [false, 'false', 0, '0', '', null],
                'constraints'  => [
                    new Callback([
                        'callback' => function (bool $isLegal, ExecutionContextInterface $context, $payload) {
                            /**
                             * @var Order $order
                             */
                            $order    = $context->getRoot()->getData();
                            $customer = $order->getCustomer();

                            if ($isLegal && (!$customer->isProfileLegal() || !$order->hasLegalAccount())) {
                                $msg = 'You can not update order to legal order';

                                $context->buildViolation($msg)->addViolation();
                            }
                        },
                        'groups'   => $options['validation_groups'],
                    ]),
                ]
            ])
            ->add('promotionCoupon', EntityType::class, [
                'class'  => PromotionCoupon::class,
                'mapped' => false,
            ])
            ->add('promotionLocked', CheckboxType::class, [
                'false_values' => [false, 'false', 0, '0', '', null],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'        => Order::class,
            'validation_groups' => ['order.update']
        ]);
    }
}
