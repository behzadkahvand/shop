<?php

namespace App\DataFixtures;

use App\Entity\OrderShipment;
use DateTimeInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class OrderShipmentFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(): void
    {
        $this->setReferenceAndPersist(
            'order_shipment_preparing',
            $this->createOrderShipment(
                'order_1',
                'transaction_1',
                'shipping_category_1',
                'shipping_method_1',
                ['order_item_1', 'order_item_2'],
                'shipping_period_1',
                $this->faker->randomNumber(),
                $this->faker->randomNumber(),
                $this->faker->dateTimeBetween('now', '+3 days'),
                'PREPARING',
                $this->faker->sentence(1),
                $this->faker->sentence(1),
                $this->faker->sentence(1),
                [1, 5],
                0
            )
        );
        $this->setReferenceAndPersist(
            'order_shipment_waitingForSend',
            $this->createOrderShipment(
                'order_2',
                'transaction_2',
                'shipping_category_1',
                'shipping_method_1',
                ['order_item_3'],
                'shipping_period_1',
                $this->faker->randomNumber(),
                $this->faker->randomNumber(),
                $this->faker->dateTimeBetween('now', '+3 days'),
                'WAITING_FOR_SEND',
                $this->faker->sentence(1),
                $this->faker->sentence(1),
                $this->faker->sentence(1),
                [1, 5],
                0
            )
        );
        $this->setReferenceAndPersist(
            'order_shipment_waitingForSupply',
            $this->createOrderShipment(
                'order_2',
                'transaction_3',
                'shipping_category_1',
                'shipping_method_2',
                ['order_item_4'],
                'shipping_period_1',
                $this->faker->randomNumber(),
                $this->faker->randomNumber(),
                $this->faker->dateTimeBetween('now', '+5 days'),
                'WAITING_FOR_SUPPLY',
                $this->faker->sentence(1),
                $this->faker->sentence(1),
                $this->faker->sentence(1),
                [2, 3],
                0
            )
        );
        $this->setReferenceAndPersist(
            'order_shipment_1',
            $this->createOrderShipment(
                'order_5',
                null,
                'shipping_category_1',
                'shipping_method_1',
                ['order_item_9'],
                'shipping_period_1',
                75000,
                75000,
                $this->faker->dateTimeBetween('now', '+10 days'),
                'SENT',
                $this->faker->sentence(1),
                $this->faker->sentence(1),
                $this->faker->sentence(1),
                [3, 4],
                0
            )
        );
        $this->setReferenceAndPersist(
            'order_shipment_2',
            $this->createOrderShipment(
                'order_5',
                null,
                'shipping_category_2',
                'shipping_method_1',
                ['order_item_10'],
                'shipping_period_1',
                90000,
                90000,
                $this->faker->dateTimeBetween('now', '+20 days'),
                'WAITING_FOR_SEND',
                $this->faker->sentence(1),
                $this->faker->sentence(1),
                $this->faker->sentence(1),
                [9, 11],
                0
            )
        );
        $this->setReferenceAndPersist(
            'order_shipment_3',
            $this->createOrderShipment(
                'order_6',
                null,
                'shipping_category_2',
                'shipping_method_1',
                ['order_item_11'],
                'shipping_period_1',
                75000,
                75000,
                $this->faker->dateTimeBetween('now', '+10 days'),
                'SENT',
                $this->faker->sentence(1),
                $this->faker->sentence(1),
                $this->faker->sentence(1),
                [1, 2],
                0
            )
        );
        $this->setReferenceAndPersist(
            'order_shipment_4',
            $this->createOrderShipment(
                'order_6',
                null,
                'shipping_category_3',
                'shipping_method_1',
                ['order_item_12'],
                'shipping_period_1',
                90000,
                90000,
                $this->faker->dateTimeBetween('now', '+20 days'),
                'WAITING_FOR_SEND',
                $this->faker->sentence(1),
                $this->faker->sentence(1),
                $this->faker->sentence(1),
                [4, 6],
                0
            )
        );
        $this->setReferenceAndPersist(
            'order_shipment_5',
            $this->createOrderShipment(
                'order_7',
                null,
                'shipping_category_3',
                'shipping_method_1',
                ['order_item_13'],
                'shipping_period_1',
                90000,
                90000,
                $this->faker->dateTimeBetween('now', '+20 days'),
                'CANCELED',
                $this->faker->sentence(1),
                $this->faker->sentence(1),
                $this->faker->sentence(1),
                [4, 6],
                0
            )
        );
        $this->setReferenceAndPersist(
            'order_shipment_6',
            $this->createOrderShipment(
                'order_2',
                'transaction_4',
                'shipping_category_3',
                'shipping_method_1',
                ['order_item_5'],
                'shipping_period_1',
                90000,
                90000,
                $this->faker->dateTimeBetween('now', '+20 days'),
                'WAITING_FOR_SEND',
                $this->faker->sentence(1),
                $this->faker->sentence(1),
                $this->faker->sentence(1),
                [4, 6],
                0
            )
        );
        $this->setReferenceAndPersist(
            'order_shipment_7',
            $this->createOrderShipment(
                'order_2',
                'transaction_5',
                'shipping_category_3',
                'shipping_method_1',
                ['order_item_6'],
                'shipping_period_1',
                90000,
                90000,
                $this->faker->dateTimeBetween('now', '+20 days'),
                'WAITING_FOR_SEND',
                $this->faker->sentence(1),
                $this->faker->sentence(1),
                $this->faker->sentence(1),
                [4, 6],
                0
            )
        );
        $this->setReferenceAndPersist(
            'order_shipment_8',
            $this->createOrderShipment(
                'order_2',
                'transaction_13',
                'shipping_category_3',
                'shipping_method_1',
                ['order_item_7'],
                'shipping_period_1',
                90000,
                90000,
                $this->faker->dateTimeBetween('now', '+20 days'),
                'WAITING_FOR_SUPPLY',
                $this->faker->sentence(1),
                $this->faker->sentence(1),
                $this->faker->sentence(1),
                [4, 6],
                0
            )
        );
        $this->setReferenceAndPersist(
            'order_shipment_9',
            $this->createOrderShipment(
                'order_2',
                'transaction_14',
                'shipping_category_3',
                'shipping_method_1',
                ['order_item_8'],
                'shipping_period_1',
                90000,
                90000,
                $this->faker->dateTimeBetween('now', '+20 days'),
                'WAITING_FOR_SUPPLY',
                $this->faker->sentence(1),
                $this->faker->sentence(1),
                'NORMAL',
                [4, 6],
                0
            )
        );
        $this->setReferenceAndPersist(
            'order_shipment_10',
            $this->createOrderShipment(
                'order_10',
                'transaction_15',
                'shipping_category_3',
                'shipping_method_1',
                ['order_item_14', 'order_item_16', 'order_item_17'],
                'shipping_period_1',
                90000,
                90000,
                $this->faker->dateTimeBetween('now', '+20 days'),
                'WAITING_FOR_SUPPLY',
                $this->faker->sentence(1),
                $this->faker->sentence(1),
                'NORMAL',
                [4, 6],
                0
            )
        );
        $this->setReferenceAndPersist(
            'order_shipment_11',
            $this->createOrderShipment(
                'order_11',
                'transaction_21',
                'shipping_category_3',
                'shipping_method_2',
                ['order_item_18', 'order_item_19', 'order_item_20'],
                'shipping_period_1',
                95000,
                95000,
                $this->faker->dateTimeBetween('now', '+20 days'),
                'WAITING_FOR_SUPPLY',
                $this->faker->sentence(1),
                $this->faker->sentence(1),
                'NORMAL',
                [4, 6],
                0
            )
        );
        $this->setReferenceAndPersist(
            'order_shipment_12',
            $this->createOrderShipment(
                'order_12',
                'transaction_22',
                'shipping_category_4',
                'shipping_method_6',
                ['order_item_21'],
                'shipping_period_1',
                95000,
                95000,
                $this->faker->dateTimeBetween('now', '+20 days'),
                'WAITING_FOR_SUPPLY',
                $this->faker->sentence(1),
                $this->faker->sentence(1),
                'NORMAL',
                [3, 5],
                0
            )
        );
        $this->setReferenceAndPersist(
            'order_shipment_13',
            $this->createOrderShipment(
                'order_10',
                null,
                'shipping_category_3',
                'shipping_method_1',
                [],
                'shipping_period_1',
                90000,
                90000,
                $this->faker->dateTimeBetween('now', '+20 days'),
                'WAITING_FOR_SUPPLY',
                $this->faker->sentence(1),
                $this->faker->sentence(1),
                'NORMAL',
                [4, 6],
                0
            )
        );
        $this->setReferenceAndPersist(
            'order_shipment_14',
            $this->createOrderShipment(
                'order_9',
                null,
                'shipping_category_4',
                'shipping_method_6',
                ['order_item_15'],
                'shipping_period_1',
                95000,
                95000,
                $this->faker->dateTimeBetween('now', '+20 days'),
                'NEW',
                $this->faker->sentence(1),
                $this->faker->sentence(1),
                'NORMAL',
                [3, 5],
                0
            )
        );
        $this->setReferenceAndPersist(
            'order_shipment_15',
            $this->createOrderShipment(
                'order_13',
                null,
                'shipping_category_4',
                'shipping_method_6',
                ['order_item_22', 'order_item_23'],
                'shipping_period_1',
                95000,
                95000,
                $this->faker->dateTimeBetween('now', '+20 days'),
                'DELIVERED',
                $this->faker->sentence(1),
                $this->faker->sentence(1),
                'NORMAL',
                [3, 5],
                0
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
            OrderItemFixtures::class,
            TransactionFixtures::class,
            ShippingPeriodFixtures::class,
            ShippingCategoryFixtures::class,
        ];
    }

    private function createOrderShipment(
        string $order,
        ?string $transaction,
        string $shippingCategory,
        string $shippingMethod,
        array $orderItems,
        string $shippingPeriod,
        int $subtotal,
        int $grandTotal,
        DateTimeInterface $deliveryDate,
        string $status,
        string $signature,
        string $trackingCode,
        string $title,
        array $categoryDeliveryRange,
        int $packageCount
    ): OrderShipment {
        $orderShipment = new OrderShipment();
        $orderShipment
            ->setOrder($this->getReference($order))
            ->setTransaction($transaction ? $this->getReference($transaction) : null)
            ->setShippingCategory($this->getReference($shippingCategory))
            ->setMethod($this->getReference($shippingMethod))
            ->setPeriod($this->getReference($shippingPeriod))
            ->setSubTotal($subtotal)
            ->setGrandTotal($grandTotal)
            ->setDeliveryDate($deliveryDate)
            ->setStatus($status)
            ->setSignature($signature)
            ->setTrackingCode($trackingCode)
            ->setTitle($title)
            ->setCategoryDeliveryRange($categoryDeliveryRange)
            ->setPackagedCount($packageCount);

        foreach ($orderItems as $item) {
            $orderShipment->addOrderItem($this->getReference($item));
        }

        return $orderShipment;
    }
}
