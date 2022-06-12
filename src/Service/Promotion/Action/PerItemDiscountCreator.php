<?php

namespace App\Service\Promotion\Action;

use App\Entity\Cart;
use App\Entity\Order;
use App\Entity\OrderShipment;
use App\Entity\PromotionAction;
use App\Service\Promotion\Action\DiscountValidation\DiscountValidatorInterface;
use App\Service\Promotion\Factory\PromotionDiscountFactoryInterface;
use App\Service\Promotion\PromotionSubjectInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;

class PerItemDiscountCreator implements DiscountCreatorInterface
{
    private ServiceLocator $promotionDiscountFactoryLocator;

    private OrderItemDiscountDistributorInterface $discountDistributor;

    public function __construct(
        ServiceLocator $promotionDiscountFactoryLocator,
        OrderItemDiscountDistributorInterface $discountDistributor
    ) {
        $this->promotionDiscountFactoryLocator = $promotionDiscountFactoryLocator;
        $this->discountDistributor = $discountDistributor;
    }

    /**
     * @param PromotionAction $action
     * @param PromotionSubjectInterface|Cart|Order $subject
     * @param array $context
     * @return array
     */
    public function create(PromotionAction $action, PromotionSubjectInterface $subject, array &$context = []): array
    {
        $promotionDiscountFactory = $this->getFactory($subject);

        if ($subject instanceof Cart) {
            $promotionAmount = $this->discountDistributor->calculateAmountForCart($subject, $action, $context);

            if (0 === $promotionAmount) {
                return [];
            }

            return [
                $promotionDiscountFactory->create($action, $promotionAmount, $subject)
            ];
        }

        $orderItemDiscounts = $this->discountDistributor->distributeForOrder($subject, $action, $context);

        foreach ($orderItemDiscounts as $orderItemDiscount) {
            $discount = $promotionDiscountFactory->create($action, $orderItemDiscount['discountAmount'], $subject);
            $orderItemDiscount['orderItem']->addDiscount($discount);
            $orderItemDiscount['orderItem']->getOrderShipment()->addDiscount($discount);
            $discount->setQuantity($orderItemDiscount['orderItem']->getQuantity());
            $discount->setUnitAmount((int) $discount->getAmount() / $orderItemDiscount['orderItem']->getQuantity());
            $discounts[] = $discount;
        }

        return $subject->getActionDiscounts($action)->toArray();
    }

    private function getFactory($subject): PromotionDiscountFactoryInterface
    {
        $className = null;
        // TODO: find a solution that follows Open-Closed principal
        switch (true) {
            case $subject instanceof Order:
                $className = Order::class;
                break;
            case $subject instanceof Cart:
                $className = Cart::class;
                break;
            default:
                get_class($subject);
        }

        return $this->promotionDiscountFactoryLocator->get($className);
    }
}
