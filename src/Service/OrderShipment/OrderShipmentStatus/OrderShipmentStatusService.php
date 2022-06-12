<?php

namespace App\Service\OrderShipment\OrderShipmentStatus;

use App\Dictionary\OrderShipmentStatus;
use App\Entity\Admin;
use App\Entity\OrderShipment;
use App\Service\OrderShipment\OrderShipmentStatus\Events\OrderShipmentStatusChanged;
use App\Service\OrderShipment\OrderShipmentStatus\Exceptions\InvalidOrderShipmentStatusException;
use App\Service\OrderShipment\OrderShipmentStatus\Log\OrderShipmentStatusLogCreator;
use Doctrine\DBAL\Exception\RetryableException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class OrderShipmentStatusService
{
    private EntityManagerInterface $manager;

    private OrderShipmentStatusFactory $orderShipmentStatusFactory;

    private EventDispatcherInterface $dispatcher;

    private OrderShipmentStatusLogCreator $statusLogCreator;

    private ManagerRegistry $managerRegistry;

    public function __construct(
        EntityManagerInterface $manager,
        OrderShipmentStatusFactory $orderShipmentStatusFactory,
        EventDispatcherInterface $dispatcher,
        OrderShipmentStatusLogCreator $statusLogCreator,
        ManagerRegistry $managerRegistry
    ) {
        $this->manager                    = $manager;
        $this->orderShipmentStatusFactory = $orderShipmentStatusFactory;
        $this->dispatcher                 = $dispatcher;
        $this->statusLogCreator           = $statusLogCreator;
        $this->managerRegistry            = $managerRegistry;
    }

    public function change(OrderShipment $orderShipment, string $nextStatus, Admin $admin = null): void
    {
        if (!OrderShipmentStatus::isValid($nextStatus)) {
            throw new InvalidOrderShipmentStatusException();
        }

        if ($orderShipment->getStatus() === $nextStatus && $nextStatus !== OrderShipmentStatus::PACKAGED) {
            return;
        }

        $oldStatus           = $orderShipment->getStatus();
        $orderShipmentStatus = $this->orderShipmentStatusFactory->create($oldStatus);
        $method              = camel_case($nextStatus);

        $retries = 0;
        do {
            $this->manager->beginTransaction();

            try {
                $orderShipmentStatus->{$method}($orderShipment);

                $this->statusLogCreator->create($orderShipment, $oldStatus, $nextStatus, $admin);

                $this->manager->flush();
                $this->manager->commit();

                $this->dispatcher->dispatch(new OrderShipmentStatusChanged($orderShipment, $oldStatus, $nextStatus));

                return;
            } catch (RetryableException $exception) {
                $this->manager->close();
                $this->manager->rollBack();
                $this->managerRegistry->resetManager();

                ++$retries;
            } catch (Exception $e) {
                $this->manager->close();
                $this->manager->rollBack();

                throw $e;
            }
        } while ($retries < 3);

        throw $exception;
    }
}
