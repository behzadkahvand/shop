<?php

namespace App\DataFixtures;

use App\Entity\OrderPromotionDiscount;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class OrderPromotionDiscountFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(): void
    {
        $this->manager->persist(
            $this->createOrderPromotionDiscount(
                'fixed_discount_4',
                'order_5',
                'order_shipment_1',
                'order_item_2',
                455
            )
        );

        $this->manager->persist(
            $this->createOrderPromotionDiscount(
                'fixed_discount_4',
                'order_5',
                'order_shipment_2',
                'order_item_10',
                545
            )
        );

        $this->manager->flush();
    }

    /**
     * @inheritdoc
     */
    public function getDependencies(): array
    {
        return [
            PromotionActionFixtures::class,
            OrderFixtures::class,
            OrderShipmentFixtures::class,
            OrderItemFixtures::class,
        ];
    }

    private function createOrderPromotionDiscount(
        string $action,
        string $subject,
        string $orderShipment,
        string $orderItem,
        int $amount,
    ): OrderPromotionDiscount {
        return (new OrderPromotionDiscount())
            ->setAction($this->getReference($action))
            ->setSubject($this->getReference($subject))
            ->setOrderItem($this->getReference($orderItem))
            ->setOrderShipment($this->getReference($orderShipment))
            ->setAmount($amount);
    }
}
