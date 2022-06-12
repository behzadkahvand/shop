<?php

namespace App\DataFixtures;

use App\Entity\Order;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class OrderFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(): void
    {
        $this->setReferenceAndPersist(
            "order_1",
            $this->createOrder(
                'WAIT_CUSTOMER',
                '12345678',
                300000,
                290000,
                290000,
                'OFFLINE',
                'customer_1',
                'order_document_1'
            )
        );
        $this->setReferenceAndPersist(
            "order_2",
            $this->createOrder(
                'WAIT_CUSTOMER',
                '12345679',
                610000,
                600000,
                600000,
                'OFFLINE',
                'customer_1',
                'order_document_2'
            )
        );
        $this->setReferenceAndPersist(
            "order_3",
            $this->createOrder(
                'WAIT_CUSTOMER',
                '12345680',
                610000,
                590000,
                590000,
                'OFFLINE',
                'customer_1',
                'order_document_3'
            )
        );
        $this->setReferenceAndPersist(
            "order_4",
            $this->createOrder(
                'WAIT_CUSTOMER',
                '12345681',
                610000,
                600000,
                600000,
                'OFFLINE',
                'customer_2',
                'order_document_4'
            )
        );
        $this->setReferenceAndPersist(
            "order_5",
            $this->createOrder(
                'CONFIRMED',
                '12345682',
                354000,
                349000,
                0,
                'ONLINE',
                'customer_1',
                'order_document_5',
                'fourth_coupon'
            )
        );
        $this->setReferenceAndPersist(
            "order_6",
            $this->createOrder(
                'CONFIRMED',
                '12345683',
                698000,
                680000,
                0,
                'ONLINE',
                'customer_1',
                'order_document_6'
            )
        );
        $this->setReferenceAndPersist(
            "order_7",
            $this->createOrder(
                'WAITING_FOR_PAY',
                '22222222',
                245000,
                245000,
                0,
                'ONLINE',
                'customer_1',
                'order_document_7'
            )
        );
        $this->setReferenceAndPersist(
            "order_8",
            $this->createOrder(
                'WAITING_FOR_PAY',
                '11111111',
                279000,
                275000,
                0,
                'ONLINE',
                'customer_3',
                'order_document_8'
            )
        );
        $this->setReferenceAndPersist(
            "order_9",
            $this->createOrder(
                'WAITING_FOR_PAY',
                '4444444444',
                279000,
                275000,
                0,
                'ONLINE',
                'customer_3',
                'order_document_9'
            )
        );
        $this->setReferenceAndPersist(
            "order_10",
            $this->createOrder(
                'WAIT_CUSTOMER',
                '878787',
                300000,
                290000,
                290000,
                'OFFLINE',
                'customer_1',
                'order_document_10'
            )
        );
        $this->setReferenceAndPersist(
            "order_11",
            $this->createOrder(
                'CONFIRMED',
                '369852147',
                380000,
                290000,
                -290000,
                'OFFLINE',
                'customer_1',
                'order_document_11'
            )
        );
        $this->setReferenceAndPersist(
            "order_12",
            $this->createOrder(
                'CONFIRMED',
                '333333444444',
                600000,
                600000,
                0,
                'OFFLINE',
                'customer_1',
                'order_document_12'
            )
        );
        $this->setReferenceAndPersist(
            "order_13",
            $this->createOrder(
                'DELIVERED',
                '34575678745',
                600000,
                600000,
                0,
                'OFFLINE',
                'customer_1',
                'order_document_13'
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
            CustomerFixtures::class,
            OrderDocumentFixtures::class,
            PromotionCouponFixtures::class,
        ];
    }

    private function createOrder(
        string $status,
        string $identifier,
        int $subtotal,
        int $grandTotal,
        int $balanceAmount,
        string $paymentMethod,
        string $customer,
        string $orderDocument,
        ?string $promotionCoupon = null
    ): Order {
        return (new Order())
            ->setStatus($status)
            ->setIdentifier($identifier)
            ->setSubtotal($subtotal)
            ->setGrandTotal($grandTotal)
            ->setBalanceAmount($balanceAmount)
            ->setPaymentMethod($paymentMethod)
            ->setCustomer($this->getReference($customer))
            ->setOrderDocument($this->getReference($orderDocument))
            ->setPromotionCoupon($promotionCoupon ? $this->getReference($promotionCoupon) : null);
    }
}
