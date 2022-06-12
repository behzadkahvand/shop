<?php

namespace App\Service\Order\OrderStatus;

use App\Entity\Admin;
use App\Entity\Order;
use App\Service\Order\OrderStatus\Events\OrderStatusChanged;
use App\Service\Order\OrderStatus\Exceptions\InvalidOrderStatusMethodException;
use App\Service\OrderStatusLog\CreateOrderStatusLogService;
use Doctrine\DBAL\Exception\RetryableException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class OrderStatusService
{
    private EntityManagerInterface $manager;

    private OrderStatusFactory $orderStatusFactory;

    private CreateOrderStatusLogService $createOrderStatusLog;

    private EventDispatcherInterface $dispatcher;

    private ManagerRegistry $managerRegistry;

    public function __construct(
        EntityManagerInterface $manager,
        OrderStatusFactory $orderStatusFactory,
        CreateOrderStatusLogService $createOrderStatusLog,
        EventDispatcherInterface $dispatcher,
        ManagerRegistry $managerRegistry
    ) {
        $this->manager              = $manager;
        $this->orderStatusFactory   = $orderStatusFactory;
        $this->createOrderStatusLog = $createOrderStatusLog;
        $this->dispatcher           = $dispatcher;
        $this->managerRegistry      = $managerRegistry;
    }

    public function change(Order $order, string $nextStatus, Admin $admin = null): void
    {
        $oldStatus = $order->getStatus();

        if ($oldStatus === $nextStatus) {
            return;
        }

        $orderStatus = $this->orderStatusFactory->create($oldStatus);

        $method = camel_case($nextStatus);

        if (!method_exists($orderStatus, $method)) {
            throw new InvalidOrderStatusMethodException();
        }

        $retries = 0;

        do {
            $this->manager->beginTransaction();

            try {
                $orderStatus->{$method}($order);

                $this->manager->flush();

                $createLogValueObj = $this->orderStatusFactory->getCreateOrderStatusLogValueObject();

                $createLogValueObj->setOrder($order)
                                  ->setStatusFrom($oldStatus)
                                  ->setStatusTo($nextStatus)
                                  ->setUser($admin);

                $this->createOrderStatusLog->perform($createLogValueObj);

                $this->manager->commit();

                $this->dispatcher->dispatch(new OrderStatusChanged($order, $oldStatus, $nextStatus));

                return;
            } catch (RetryableException $exception) {
                $this->manager->close();
                $this->manager->rollBack();
                $this->managerRegistry->resetManager();

                ++$retries;
            } catch (Exception $exception) {
                $this->manager->close();
                $this->manager->rollBack();

                throw $exception;
            }
        } while ($retries < 3);

        throw $exception;
    }
}
