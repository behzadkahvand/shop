<?php

namespace App\Service\OrderItemLog;

use App\Entity\Admin;
use App\Entity\OrderItem;
use App\Entity\OrderItemLog;
use Doctrine\ORM\EntityManagerInterface;

class OrderItemLogCreator
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function create(OrderItem $orderItem, int $quantityFrom, int $quantityTo, Admin $admin = null): OrderItemLog
    {
        $orderItemLog = (new OrderItemLog())
            ->setQuantityFrom($quantityFrom)
            ->setQuantityTo($quantityTo)
            ->setUser($admin);

        $orderItem->addLog($orderItemLog);

        $this->entityManager->persist($orderItemLog);

        return $orderItemLog;
    }
}
