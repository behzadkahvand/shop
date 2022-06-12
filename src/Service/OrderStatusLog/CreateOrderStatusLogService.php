<?php

namespace App\Service\OrderStatusLog;

use App\Service\OrderStatusLog\ValueObjects\CreateOrderStatusLogValueObject;
use Doctrine\ORM\EntityManagerInterface;

class CreateOrderStatusLogService
{
    protected EntityManagerInterface $manager;

    protected OrderStatusLogFactory $factory;

    public function __construct(
        EntityManagerInterface $manager,
        OrderStatusLogFactory $factory
    ) {
        $this->manager = $manager;
        $this->factory = $factory;
    }

    public function perform(CreateOrderStatusLogValueObject $valueObject, bool $performFlush = true): void
    {
        $orderStatusLog = $this->factory->getOrderStatusLog();

        $orderStatusLog->setOrder($valueObject->getOrder())
            ->setStatusFrom($valueObject->getStatusFrom())
            ->setStatusTo($valueObject->getStatusTo())
            ->setUser($valueObject->getUser());

        $this->manager->persist($orderStatusLog);

        if ($performFlush) {
            $this->manager->flush();
        }
    }
}
