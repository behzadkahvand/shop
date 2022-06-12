<?php

namespace App\Service\OrderShipment\OrderShipmentStatus\Log;

use App\Entity\Admin;
use App\Entity\OrderShipment;
use App\Entity\OrderShipmentStatusLog;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class OrderShipmentStatusLogCreator
 */
class OrderShipmentStatusLogCreator
{
    private EntityManagerInterface $entityManager;

    /**
     * OrderShipmentStatusLogCreator constructor.
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param OrderShipment $orderShipment
     * @param string        $from
     * @param string        $to
     * @param Admin|null    $admin
     */
    public function create(OrderShipment $orderShipment, string $from, string $to, Admin $admin = null): void
    {
        $log = (new OrderShipmentStatusLog())
            ->setStatusFrom($from)
            ->setStatusTo($to)
            ->setUser($admin);

        $orderShipment->addOrderShipmentStatusLog($log);

        $this->entityManager->persist($log);
    }
}
