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

/**
 * @deprecated, use App\Service\Promotion\Action\PerItemDiscountCreator instead
 */
class DiscountCreator implements DiscountCreatorInterface
{
    private ServiceLocator $promotionDiscountFactoryLocator;

    private DiscountValidatorInterface $discountValidator;

    public function __construct(
        ServiceLocator $promotionDiscountFactoryLocator,
        DiscountValidatorInterface $discountValidator
    ) {
        $this->promotionDiscountFactoryLocator = $promotionDiscountFactoryLocator;
        $this->discountValidator = $discountValidator;
    }

    public function create(PromotionAction $action, PromotionSubjectInterface $subject, array &$context = []): array
    {
        $configuration = $action->getConfiguration();
        $amount = min($subject->getPromotionSubjectTotal(), $configuration['amount']);

        if (0 === $amount) {
            return false;
        }
        $promotionDiscountFactory = $this->getFactory($subject);

        if (!$subject instanceof Order) {
            return [
                $promotionDiscountFactory->create($action, $amount, $subject)
            ];
        }

        $shipmentsInfos = $subject->getShipments()
            ->map(fn(OrderShipment $shipment) => array_merge(['shipment' => $shipment], $shipment->getOrderItemsInfo()))
        ;

        if (isset($context['inventory_ids']) && !empty($context['inventory_ids'])) {
            $inventoryIds = $context['inventory_ids'];
            $shipmentsInfos->map(function ($shipmentInfo) use ($inventoryIds) {
                $itemsGrandTotal = array_sum(array_filter(
                    $shipmentInfo['inventory_ids'],
                    fn($grandTotal, $inventoryId) => in_array($inventoryId, $inventoryIds),
                    ARRAY_FILTER_USE_BOTH
                ));
                $shipmentInfo['items_grand_total'] = $itemsGrandTotal;

                return $shipmentInfo;
            });
            $shipmentsInfos = $shipmentsInfos->filter(fn($shipmentInfo) => $shipmentInfo['items_grand_total'] > 0);
        }

        $discounts = [];
        $shipmentDiscounts = [];
        $totalShipmentDiscount = 0;
        foreach ($subject->getActionDiscounts($action) as $actionDiscount) {
            $totalShipmentDiscount += $actionDiscount->getAmount();
        }
        $shipmentsInfos = $shipmentsInfos->toArray();
        array_multisort(array_column($shipmentsInfos, 'items_grand_total'), SORT_ASC, $shipmentsInfos);
        foreach ($shipmentsInfos as $shipmentInfo) {
            if (!$this->discountValidator->shouldApply($subject, ['shipment' => $shipmentInfo['shipment']])) {
                continue;
            }

            $remainingDiscountAmount = $amount - $totalShipmentDiscount;

            if ($remainingDiscountAmount <= 0) {
                break;
            }

            $shipmentDiscountAmount = min($remainingDiscountAmount, $shipmentInfo['items_grand_total']);

            if ($shipmentDiscountAmount === 0) {
                continue;
            }

            $shipmentDiscounts[] = [
                'shipment' => $shipmentInfo['shipment'],
                'discount' => $shipmentDiscountAmount,
            ];

            $totalShipmentDiscount += $shipmentDiscountAmount;
        }

        if ($totalShipmentDiscount < $amount && count($shipmentDiscounts) > 0) {
            $shipmentDiscounts[0]['discount'] += $amount - $totalShipmentDiscount;
        }

        foreach ($shipmentDiscounts as $shipmentDiscount) {
            $discount = $promotionDiscountFactory->create($action, $shipmentDiscount['discount'], $subject);
            $shipmentDiscount['shipment']->addDiscount($discount);
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
