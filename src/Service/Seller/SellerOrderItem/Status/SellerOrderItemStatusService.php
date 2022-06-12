<?php

namespace App\Service\Seller\SellerOrderItem\Status;

use App\Entity\SellerOrderItem;
use App\Service\Seller\SellerOrderItem\Exceptions\InvalidSellerOrderStatusException;
use App\Service\Seller\SellerOrderItem\Status\Exceptions\InvalidSellerOrderItemStatusTransitionException;
use App\Service\Seller\SellerOrderItemStatusLog\CreateSellerOrderItemStatusLogService;
use App\Service\Seller\SellerOrderItemStatusLog\ValueObjects\CreateSellerOrderItemStatusLogValueObject;
use App\Service\Seller\SellerPackage\Events\SellerOrderItemStatusChangeEvent;
use Doctrine\DBAL\Exception\RetryableException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SellerOrderItemStatusService
{
    private EntityManagerInterface $manager;

    private SellerOrderItemStatusFactory $statusFactory;

    private CreateSellerOrderItemStatusLogService $createSellerOrderItemStatusLogService;

    private EventDispatcherInterface $eventDispatcher;

    private ManagerRegistry $managerRegistry;

    public function __construct(
        EntityManagerInterface $manager,
        SellerOrderItemStatusFactory $statusFactory,
        CreateSellerOrderItemStatusLogService $createSellerOrderItemStatusLogService,
        EventDispatcherInterface $eventDispatcher,
        ManagerRegistry $managerRegistry
    ) {
        $this->manager                               = $manager;
        $this->statusFactory                         = $statusFactory;
        $this->createSellerOrderItemStatusLogService = $createSellerOrderItemStatusLogService;
        $this->eventDispatcher                       = $eventDispatcher;
        $this->managerRegistry                       = $managerRegistry;
    }

    /**
     * @param SellerOrderItem $sellerOrderItem
     * @param string $nextStatus
     *
     * @throws InvalidSellerOrderItemStatusTransitionException
     * @throws InvalidSellerOrderStatusException
     * @throws RetryableException
     */
    public function change(SellerOrderItem $sellerOrderItem, string $nextStatus): void
    {
        $oldStatus = $sellerOrderItem->getStatus();

        if ($oldStatus === $nextStatus) {
            return;
        }

        $currentStatus = $this->statusFactory->create($oldStatus);
        $method        = camel_case($nextStatus);

        if (!method_exists($currentStatus, $method)) {
            throw new InvalidSellerOrderItemStatusTransitionException($oldStatus, $nextStatus);
        }

        $retries = 0;
        do {
            $this->manager->beginTransaction();

            try {
                $currentStatus->{$method}($sellerOrderItem);

                $this->logStatus($sellerOrderItem, $oldStatus, $nextStatus);

                $this->manager->flush();
                $this->manager->commit();

                $this->eventDispatcher->dispatch(new SellerOrderItemStatusChangeEvent($sellerOrderItem, $oldStatus, $nextStatus));

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

    private function logStatus(SellerOrderItem $sellerOrderItem, $oldStatus, $nextStatus): void
    {
        $createLogValueObj = new CreateSellerOrderItemStatusLogValueObject();

        $createLogValueObj->setSellerOrderItem($sellerOrderItem)
                          ->setStatusFrom($oldStatus)
                          ->setStatusTo($nextStatus);

        $this->createSellerOrderItemStatusLogService->perform($createLogValueObj, false);
    }
}
