<?php

namespace App\DataFixtures;

use App\Entity\OrderStatusLog;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class OrderStatusLogFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(): void
    {
        $this->manager->persist(
            $this->createOrderStatusLog(
                'order_11',
                'admin_1',
                'NEW',
                'WAITING_FOR_PAY'
            )
        );
        $this->manager->persist(
            $this->createOrderStatusLog(
                'order_11',
                'admin_1',
                'WAITING_FOR_PAY',
                'CANCELED_SYSTEM'
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
            OrderFixtures::class,
            AdminFixtures::class,
        ];
    }

    private function createOrderStatusLog(
        string $order,
        ?string $admin,
        string $statusFrom,
        string $statusTo,
    ): OrderStatusLog {
        return (new OrderStatusLog())
            ->setOrder($this->getReference($order))
            ->setUser($admin ? $this->getReference($admin) : null)
            ->setStatusFrom($statusFrom)
            ->setStatusTo($statusTo);
    }
}
