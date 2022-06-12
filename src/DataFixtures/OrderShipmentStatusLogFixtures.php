<?php

namespace App\DataFixtures;

use App\Entity\OrderShipmentStatusLog;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class OrderShipmentStatusLogFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(): void
    {
        $this->manager->persist(
            $this->createOrderShipmentStatusLog(
                'order_shipment_11',
                'admin_1',
                'NEW',
                'WAITING_FOR_SEND'
            )
        );
        $this->manager->persist(
            $this->createOrderShipmentStatusLog(
                'order_shipment_11',
                null,
                'WAITING_FOR_SEND',
                'SENT'
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
            OrderShipmentFixtures::class,
            AdminFixtures::class,
        ];
    }

    private function createOrderShipmentStatusLog(
        string $orderShipment,
        ?string $admin,
        string $statusFrom,
        string $statusTo,
    ): OrderShipmentStatusLog {
        return (new OrderShipmentStatusLog())
            ->setOrderShipment($this->getReference($orderShipment))
            ->setUser($admin ? $this->getReference($admin) : null)
            ->setStatusFrom($statusFrom)
            ->setStatusTo($statusTo);
    }
}
