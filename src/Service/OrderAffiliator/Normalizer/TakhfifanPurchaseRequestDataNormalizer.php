<?php

namespace App\Service\OrderAffiliator\Normalizer;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\OrderShipment;
use App\Repository\OrderRepository;
use Doctrine\Common\Collections\Collection;

class TakhfifanPurchaseRequestDataNormalizer implements AffiliatorPurchaseRequestDataNormalizerInterface
{
    protected OrderRepository $orderRepository;

    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function normalize(Order $order): array
    {
        $affiliator      = $order->getAffiliator();
        $promotionCoupon = $order->getPromotionCoupon();

        return [
            "token"          => $affiliator->getUtmToken(),
            "transaction_id" => $order->getIdentifier(),
            "revenue"        => $order->getGrandTotal(),
            "shipping"       => collect($order->getShipments())->sum(fn(OrderShipment $shipment) => $shipment->getGrandTotal()),
            "tax"            => 0,
            "discount"       => (int)$order->getDiscountTotal(),
            "new_customer"   => $this->orderRepository->countByCustomer($order->getCustomer()) <= 1,
            "affiliation"    => $affiliator->getUtmSource(),
            "items"          => $this->normalizeOrderItems($order->getOrderItems()),
            "coupon_code"    => $promotionCoupon ? $promotionCoupon->getCode() : null
        ];
    }

    /**
     * @return array[]
     */
    protected function normalizeOrderItems(Collection $orderItems): array
    {
        $data = [];
        foreach ($orderItems as $orderItem) {
            /**
             * @var OrderItem $orderItem
             */
            $inventory = $orderItem->getInventory();

            $product     = $inventory->getVariant()->getProduct();
            $inventoryId = $inventory->getId();
            $quantity    = $orderItem->getQuantity();

            $data[] = [
                "sku"        => $inventoryId,
                "category"   => $product->getCategory()->getTitle(),
                "product_id" => (string)$inventoryId,
                "price"      => $orderItem->getGrandTotal() / $quantity,
                "quantity"   => $quantity,
                "name"       => $product->getTitle(),
            ];
        }

        return $data;
    }
}
