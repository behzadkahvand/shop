<?php

namespace App\DataFixtures;

use App\Entity\OrderItemLog;
use DateTimeInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class OrderItemLogFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(): void
    {
        $this->manager->persist(
            $this->createOrderItemLog(
                'order_item_18',
                'admin_1',
                0,
                1,
                $this->faker->dateTimeBetween('-3 days')
            )
        );
        $this->manager->persist(
            $this->createOrderItemLog(
                'order_item_19',
                'admin_1',
                0,
                1,
                $this->faker->dateTimeBetween('-2 days')
            )
        );
        $this->manager->persist(
            $this->createOrderItemLog(
                'order_item_20',
                'admin_1',
                0,
                1,
                $this->faker->dateTimeBetween('-3 days')
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
            AdminFixtures::class,
        ];
    }

    private function createOrderItemLog(
        string $orderItemName,
        string $userName,
        int $quantityFrom,
        int $quantityTo,
        DateTimeInterface $createdAt,
    ): OrderItemLog {
        return (new OrderItemLog())
            ->setOrderItem($this->getReference($orderItemName))
            ->setUser($this->getReference($userName))
            ->setQuantityFrom($quantityFrom)
            ->setQuantityTo($quantityTo)
            ->setCreatedAt($createdAt);
    }
}
