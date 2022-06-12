<?php

namespace App\DataFixtures;

use App\Dictionary\SellerOrderItemStatus;
use App\Entity\SellerOrderItem;
use DateTimeInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class SellerOrderItemFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(): void
    {
        $this->setReferenceAndPersist(
            'seller_order_item_1',
            $this->createSellerOrderItem(
                SellerOrderItemStatus::SENT_BY_SELLER,
                'order_item_1',
                'seller_lendo',
                'seller_package_item_1',
                $this->faker->dateTimeBetween("-10 day", "+5 day")
            )
        );

        $this->setReferenceAndPersist(
            'seller_order_item_2',
            $this->createSellerOrderItem(
                SellerOrderItemStatus::WAITING_FOR_SEND,
                'order_item_2',
                'seller_lendo',
                'seller_package_item_2',
                $this->faker->dateTimeBetween("-10 day", "+5 day")
            )
        );

        $this->setReferenceAndPersist(
            'seller_order_item_3',
            $this->createSellerOrderItem(
                SellerOrderItemStatus::WAITING_FOR_SEND,
                'order_item_3',
                'seller_lendo',
                'seller_package_item_3',
                $this->faker->dateTimeBetween("-10 day", "+5 day")
            )
        );

        $this->setReferenceAndPersist(
            'seller_order_item_4',
            $this->createSellerOrderItem(
                SellerOrderItemStatus::SENT_BY_SELLER,
                'order_item_4',
                'seller_lendo',
                'seller_package_item_1',
                $this->faker->dateTimeBetween("-10 day", "+5 day")
            )
        );

        $this->setReferenceAndPersist(
            'seller_order_item_5',
            $this->createSellerOrderItem(
                SellerOrderItemStatus::CANCELED_BY_SELLER,
                'order_item_5',
                'seller_lendo',
                'seller_package_item_2',
                $this->faker->dateTimeBetween("-10 day", "+5 day")
            )
        );

        $this->setReferenceAndPersist(
            'seller_order_item_6',
            $this->createSellerOrderItem(
                SellerOrderItemStatus::WAITING_FOR_SEND,
                'order_item_6',
                'seller_lendo',
                'seller_package_item_3',
                $this->faker->dateTimeBetween("-10 day", "+5 day")
            )
        );

        $this->setReferenceAndPersist(
            'seller_order_item_7',
            $this->createSellerOrderItem(
                SellerOrderItemStatus::SENT_BY_SELLER,
                'order_item_7',
                'seller_lendo',
                'seller_package_item_1',
                $this->faker->dateTimeBetween("-10 day", "+5 day")
            )
        );

        $this->setReferenceAndPersist(
            'seller_order_item_8',
            $this->createSellerOrderItem(
                SellerOrderItemStatus::CANCELED_BY_SELLER,
                'order_item_8',
                'seller_lendo',
                'seller_package_item_2',
                $this->faker->dateTimeBetween("-10 day", "+5 day")
            )
        );

        $this->setReferenceAndPersist(
            'seller_order_item_9',
            $this->createSellerOrderItem(
                SellerOrderItemStatus::WAITING_FOR_SEND,
                'order_item_9',
                'seller_lendo',
                'seller_package_item_3',
                $this->faker->dateTimeBetween("-10 day", "+5 day")
            )
        );

        $this->setReferenceAndPersist(
            'seller_order_item_10',
            $this->createSellerOrderItem(
                SellerOrderItemStatus::WAITING_FOR_SEND,
                'order_item_10',
                'seller_lendo',
                'seller_package_item_3',
                $this->faker->dateTimeBetween("-10 day", "+5 day")
            )
        );

        $this->setReferenceAndPersist(
            'seller_order_item_11',
            $this->createSellerOrderItem(
                SellerOrderItemStatus::WAITING_FOR_SEND,
                'order_item_11',
                'seller_lendo',
                'seller_package_item_4',
                $this->faker->dateTimeBetween("-10 day", "+5 day")
            )
        );

        $this->setReferenceAndPersist(
            'seller_order_item_12',
            $this->createSellerOrderItem(
                SellerOrderItemStatus::WAITING_FOR_SEND,
                'order_item_12',
                'seller_lendo',
                'seller_package_item_4',
                $this->faker->dateTimeBetween("-10 day", "+5 day")
            )
        );

        $this->setReferenceAndPersist(
            'seller_order_item_13',
            $this->createSellerOrderItem(
                SellerOrderItemStatus::WAITING_FOR_SEND,
                'order_item_13',
                'seller_lendo',
                'seller_package_item_4'
            )
        );

        $this->setReferenceAndPersist(
            'seller_order_item_14',
            $this->createSellerOrderItem(
                SellerOrderItemStatus::WAITING_FOR_SEND,
                'order_item_14',
                'seller_lendo',
                'seller_package_item_4'
            )
        );

        $this->setReferenceAndPersist(
            'seller_order_item_15',
            $this->createSellerOrderItem(
                SellerOrderItemStatus::WAITING_FOR_SEND,
                'order_item_15',
                'seller_1',
                'seller_package_item_5'
            )
        );

        $this->setReferenceAndPersist(
            'seller_order_item_16',
            $this->createSellerOrderItem(
                SellerOrderItemStatus::WAITING_FOR_SEND,
                'order_item_16',
                'seller_1',
                'seller_package_item_6'
            )
        );

        $this->setReferenceAndPersist(
            'seller_order_item_17',
            $this->createSellerOrderItem(
                SellerOrderItemStatus::WAITING_FOR_SEND,
                'order_item_17',
                'seller_1',
                'seller_package_item_6'
            )
        );

        $this->setReferenceAndPersist(
            'seller_order_item_18',
            $this->createSellerOrderItem(
                SellerOrderItemStatus::WAITING_FOR_SEND,
                'order_item_18',
                'seller_1',
                'seller_package_item_6'
            )
        );

        $this->setReferenceAndPersist(
            'seller_order_item_19',
            $this->createSellerOrderItem(
                SellerOrderItemStatus::WAITING_FOR_SEND,
                'order_item_19',
                'seller_1',
                'seller_package_item_6'
            )
        );

        $this->setReferenceAndPersist(
            'seller_order_item_20',
            $this->createSellerOrderItem(
                SellerOrderItemStatus::WAITING_FOR_SEND,
                'order_item_20',
                'seller_1',
                'seller_package_item_6'
            )
        );

        $this->setReferenceAndPersist(
            'seller_order_item_21',
            $this->createSellerOrderItem(
                SellerOrderItemStatus::WAITING_FOR_SEND,
                'order_item_21',
                'seller_10',
                'seller_package_item_7'
            )
        );

        $this->setReferenceAndPersist(
            'seller_order_item_22',
            $this->createSellerOrderItem(
                SellerOrderItemStatus::DELIVERED,
                'order_item_22',
                'seller_10',
                'seller_package_item_8'
            )
        );

        $this->setReferenceAndPersist(
            'seller_order_item_23',
            $this->createSellerOrderItem(
                SellerOrderItemStatus::DELIVERED,
                'order_item_23',
                'seller_10',
                'seller_package_item_9'
            )
        );

        $this->manager->flush();
    }

    private function createSellerOrderItem(
        string $status,
        string $orderItem,
        string $seller,
        string $packageItem,
        ?DateTimeInterface $sendDate = null
    ): SellerOrderItem {
        $sellerOrderItem = (new SellerOrderItem())
            ->setStatus($status)
            ->setOrderItem($this->getReference($orderItem))
            ->setSeller($this->getReference($seller))
            ->setPackageItem($this->getReference($packageItem));

        if ($sendDate) {
            $sellerOrderItem->setSendDate($sendDate);
        }

        return $sellerOrderItem;
    }

    /**
     * @inheritdoc
     */
    public function getDependencies(): array
    {
        return [
            OrderItemFixtures::class,
            SellerFixtures::class,
            SellerPackageItemFixtures::class
        ];
    }
}
