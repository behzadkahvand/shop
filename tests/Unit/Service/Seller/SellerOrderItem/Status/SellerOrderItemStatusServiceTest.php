<?php

namespace App\Tests\Unit\Service\Seller\SellerOrderItem\Status;

use App\Dictionary\SellerOrderItemStatus;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\SellerOrderItem;
use App\Service\OrderShipment\OrderShipmentStatus\OrderShipmentStatusService;
use App\Service\Seller\SellerOrderItem\Status\AbstractSellerOrderItemStatus;
use App\Service\Seller\SellerOrderItem\Status\Exceptions\InvalidSellerOrderItemStatusTransitionException;
use App\Service\Seller\SellerOrderItem\Status\SellerOrderItemStatusFactory;
use App\Service\Seller\SellerOrderItem\Status\SellerOrderItemStatusService;
use App\Service\Seller\SellerOrderItemStatusLog\CreateSellerOrderItemStatusLogService;
use App\Service\Seller\SellerOrderItemStatusLog\ValueObjects\CreateSellerOrderItemStatusLogValueObject;
use App\Service\Seller\SellerPackage\Events\SellerOrderItemStatusChangeEvent;
use Doctrine\DBAL\Driver\Mysqli\Exception\ConnectionFailed;
use Doctrine\DBAL\Exception\DeadlockException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class SellerOrderItemStatusServiceTest
 */
final class SellerOrderItemStatusServiceTest extends MockeryTestCase
{
    public function testItDoNothingIfNewStatusEqualsOldStatus(): void
    {
        $sellerOrderItem = Mockery::mock(SellerOrderItem::class);
        $sellerOrderItem->shouldReceive('getStatus')
                        ->once()
                        ->withNoArgs()
                        ->andReturn(SellerOrderItemStatus::WAITING);

        $manager = Mockery::mock(EntityManagerInterface::class);
        $manager->shouldNotReceive('beginTransaction');

        $statusFactory = Mockery::mock(SellerOrderItemStatusFactory::class);
        $statusFactory->shouldNotReceive('create');

        $logService = Mockery::mock(CreateSellerOrderItemStatusLogService::class);
        $logService->shouldNotReceive('perform');

        $eventDispatcher = Mockery::mock(EventDispatcherInterface::class);
        $managerRegistry = Mockery::mock(ManagerRegistry::class);

        $service = new SellerOrderItemStatusService(
            $manager,
            $statusFactory,
            $logService,
            $eventDispatcher,
            $managerRegistry
        );

        $service->change($sellerOrderItem, SellerOrderItemStatus::WAITING);
    }

    public function testItThrowExceptionIfTransitionIsInvalid(): void
    {
        $oldStatus = SellerOrderItemStatus::WAITING;

        $sellerOrderItem = Mockery::mock(SellerOrderItem::class);
        $sellerOrderItem->shouldReceive('getStatus')
                        ->once()
                        ->withNoArgs()
                        ->andReturn($oldStatus);

        $manager = Mockery::mock(EntityManagerInterface::class);
        $manager->shouldNotReceive('beginTransaction');

        $statusFactory = Mockery::mock(SellerOrderItemStatusFactory::class);
        $statusFactory->shouldReceive('create')->once()->with($oldStatus)->andReturn(
            new class (Mockery::mock(OrderShipmentStatusService::class)) extends AbstractSellerOrderItemStatus {
                protected function getName(): string
                {
                    return 'status_object';
                }
            }
        );

        $logService = Mockery::mock(CreateSellerOrderItemStatusLogService::class);
        $logService->shouldNotReceive('perform');

        $eventDispatcher = Mockery::mock(EventDispatcherInterface::class);
        $managerRegistry = Mockery::mock(ManagerRegistry::class);

        $service = new SellerOrderItemStatusService(
            $manager,
            $statusFactory,
            $logService,
            $eventDispatcher,
            $managerRegistry
        );

        $this->expectException(InvalidSellerOrderItemStatusTransitionException::class);

        $service->change($sellerOrderItem, 'invalid_status');
    }

    public function testItRollbackTransactionIfExceptionOccur(): void
    {
        $oldStatus = SellerOrderItemStatus::WAITING;

        $sellerOrderItem = Mockery::mock(SellerOrderItem::class);
        $sellerOrderItem->shouldReceive('getStatus')
                        ->once()
                        ->withNoArgs()
                        ->andReturn($oldStatus);

        $manager = Mockery::mock(EntityManagerInterface::class);
        $manager->shouldReceive('beginTransaction')->once()->withNoArgs()->andReturn();
        $manager->shouldReceive('rollback')->once()->withNoArgs()->andReturn();
        $manager->shouldReceive('close')->once()->withNoArgs()->andReturn();

        $statusFactory = Mockery::mock(SellerOrderItemStatusFactory::class);
        $statusFactory->shouldReceive('create')->once()->with($oldStatus)->andReturn(
            new class (Mockery::mock(OrderShipmentStatusService::class)) extends AbstractSellerOrderItemStatus {
                public function sentBySeller(SellerOrderItem $sellerOrderItem): void
                {
                    throw new \Exception();
                }

                protected function getName(): string
                {
                    return 'status_object';
                }
            }
        );

        $logService = Mockery::mock(CreateSellerOrderItemStatusLogService::class);
        $logService->shouldNotReceive('perform');

        $eventDispatcher = Mockery::mock(EventDispatcherInterface::class);
        $managerRegistry = Mockery::mock(ManagerRegistry::class);

        $service = new SellerOrderItemStatusService(
            $manager,
            $statusFactory,
            $logService,
            $eventDispatcher,
            $managerRegistry
        );

        $this->expectException(\Exception::class);

        $service->change($sellerOrderItem, SellerOrderItemStatus::SENT_BY_SELLER);
    }

    public function testItChangeSellerOrderItemStatus(): void
    {
        $oldStatus = SellerOrderItemStatus::WAITING;

        $sellerOrderItem = Mockery::mock(SellerOrderItem::class);
        $orderItem       = Mockery::mock(OrderItem::class);
        $order           = Mockery::mock(Order::class);
        $sellerOrderItem->shouldReceive('getStatus')
                        ->once()
                        ->withNoArgs()
                        ->andReturn($oldStatus);
        $sellerOrderItem->shouldReceive('setStatus')
                        ->once()
                        ->with(SellerOrderItemStatus::SENT_BY_SELLER)
                        ->andReturnSelf();

        $manager = Mockery::mock(EntityManagerInterface::class);
        $manager->shouldReceive('beginTransaction')->once()->withNoArgs()->andReturn();
        $manager->shouldReceive('flush')->once()->withNoArgs()->andReturn();
        $manager->shouldReceive('commit')->once()->withNoArgs()->andReturn();
        $manager->shouldNotReceive('rollback');

        $statusFactory = Mockery::mock(SellerOrderItemStatusFactory::class);
        $statusFactory->shouldReceive('create')->once()->with($oldStatus)->andReturn(
            new class (Mockery::mock(OrderShipmentStatusService::class)) extends AbstractSellerOrderItemStatus {
                protected function getName(): string
                {
                    return 'status_object';
                }
            }
        );

        $logService = Mockery::mock(CreateSellerOrderItemStatusLogService::class);
        $logService->shouldReceive('perform')
                   ->once()
                   ->with(Mockery::type(CreateSellerOrderItemStatusLogValueObject::class), false)
                   ->andReturn();

        $eventDispatcher = Mockery::mock(EventDispatcherInterface::class);
        $eventDispatcher->shouldReceive('dispatch')->once()
                        ->with(Mockery::type(SellerOrderItemStatusChangeEvent::class))
                        ->andReturn(new \stdClass());

        $managerRegistry = Mockery::mock(ManagerRegistry::class);

        $service = new SellerOrderItemStatusService(
            $manager,
            $statusFactory,
            $logService,
            $eventDispatcher,
            $managerRegistry
        );

        $service->change($sellerOrderItem, SellerOrderItemStatus::SENT_BY_SELLER);
    }

    public function testItThrowsExceptionAndRollbackWhenOneTimeRetryableExceptionOccur(): void
    {
        $oldStatus = SellerOrderItemStatus::WAITING;

        $sellerOrderItem = Mockery::mock(SellerOrderItem::class);
        $sellerOrderItem->shouldReceive('getStatus')
                        ->once()
                        ->withNoArgs()
                        ->andReturn($oldStatus);
        $sellerOrderItem->shouldReceive('setStatus')
                        ->times(3)
                        ->with(SellerOrderItemStatus::SENT_BY_SELLER)
                        ->andReturnSelf();

        $manager = Mockery::mock(EntityManagerInterface::class);
        $manager->shouldReceive('beginTransaction')->times(3)->withNoArgs()->andReturn();
        $manager->shouldReceive('flush')
                ->times(3)
                ->withNoArgs()
                ->andThrow(new DeadlockException('', new class () extends ConnectionFailed {
                    public function __construct()
                    {
                        parent::__construct('', null, null);
                    }
                }));

        $manager->shouldNotReceive('commit');
        $manager->shouldReceive('rollback')
                ->times(3)
                ->withNoArgs()
                ->andReturn();
        $manager->shouldReceive('close')
                ->times(3)
                ->withNoArgs()
                ->andReturn();

        $statusFactory = Mockery::mock(SellerOrderItemStatusFactory::class);
        $statusFactory->shouldReceive('create')->once()->with($oldStatus)->andReturn(
            new class (Mockery::mock(OrderShipmentStatusService::class)) extends AbstractSellerOrderItemStatus {
                protected function getName(): string
                {
                    return 'status_object';
                }
            }
        );

        $logService = Mockery::mock(CreateSellerOrderItemStatusLogService::class);
        $logService->shouldReceive('perform')
                   ->times(3)
                   ->with(Mockery::type(CreateSellerOrderItemStatusLogValueObject::class), false)
                   ->andReturn();

        $eventDispatcher = Mockery::mock(EventDispatcherInterface::class);

        $managerRegistry = Mockery::mock(ManagerRegistry::class);
        $managerRegistry->shouldReceive('resetManager')
                        ->times(3)
                        ->withNoArgs()
                        ->andReturn();

        $service = new SellerOrderItemStatusService(
            $manager,
            $statusFactory,
            $logService,
            $eventDispatcher,
            $managerRegistry
        );

        $this->expectException(DeadlockException::class);

        $service->change($sellerOrderItem, SellerOrderItemStatus::SENT_BY_SELLER);
    }
}
