<?php

namespace App\Tests\Unit\Service\OrderShipment\OrderShipmentStatus;

use App\Dictionary\OrderShipmentStatus;
use App\Entity\Admin;
use App\Entity\Order;
use App\Entity\OrderShipment;
use App\Service\Order\OrderStatus\OrderStatusService;
use App\Service\Order\RecalculateOrderDocument\RecalculateOrderDocument;
use App\Service\OrderShipment\OrderShipmentStatus\AbstractOrderShipmentStatus;
use App\Service\OrderShipment\OrderShipmentStatus\Events\OrderShipmentStatusChanged;
use App\Service\OrderShipment\OrderShipmentStatus\Exceptions\InvalidOrderShipmentStatusException;
use App\Service\OrderShipment\OrderShipmentStatus\Log\OrderShipmentStatusLogCreator;
use App\Service\OrderShipment\OrderShipmentStatus\OrderShipmentStatusFactory;
use App\Service\OrderShipment\OrderShipmentStatus\OrderShipmentStatusService;
use App\Service\Seller\SellerOrderItem\Status\SellerOrderItemStatusService;
use App\Tests\Unit\BaseUnitTestCase;
use Doctrine\DBAL\Driver\Mysqli\Exception\ConnectionFailed;
use Doctrine\DBAL\Exception\DeadlockException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Mockery;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class OrderShipmentStatusServiceTest extends BaseUnitTestCase
{
    public function testItThrowExceptionIfGivenStatusIsInvalid(): void
    {
        $service = new OrderShipmentStatusService(
            Mockery::mock(EntityManagerInterface::class),
            Mockery::mock(OrderShipmentStatusFactory::class),
            Mockery::mock(EventDispatcherInterface::class),
            Mockery::mock(OrderShipmentStatusLogCreator::class),
            Mockery::mock(ManagerRegistry::class)
        );

        $this->expectException(InvalidOrderShipmentStatusException::class);

        $service->change(new OrderShipment(), 'INVALID_STATUS');
    }

    public function testItDoNothingIfOrderShipmentStatusIsEqualToNextStatus(): void
    {
        $manager = Mockery::mock(EntityManagerInterface::class);
        $manager->shouldNotReceive('beginTransaction');

        $orderShipmentStatusFactory = Mockery::mock(OrderShipmentStatusFactory::class);
        $orderShipmentStatusFactory->shouldNotReceive('create');

        $logCreator = Mockery::mock(OrderShipmentStatusLogCreator::class);
        $logCreator->shouldNotReceive('create');

        $service = new OrderShipmentStatusService(
            $manager,
            $orderShipmentStatusFactory,
            Mockery::mock(EventDispatcherInterface::class),
            $logCreator,
            Mockery::mock(ManagerRegistry::class)
        );

        $orderShipment = new OrderShipment();
        $orderShipment->setStatus(OrderShipmentStatus::WAITING_FOR_SEND);

        $service->change($orderShipment, OrderShipmentStatus::WAITING_FOR_SEND);
    }

    public function testItCanChangeOrderShipmentStatus(): void
    {
        $orderStatusService       = Mockery::mock(OrderStatusService::class);
        $sellerOrderStatusService = Mockery::mock(SellerOrderItemStatusService::class);
        $recalculateDocumentMock  = Mockery::mock(RecalculateOrderDocument::class);

        $args = [$orderStatusService, $sellerOrderStatusService, $recalculateDocumentMock];

        $statusObject = new class (...$args) extends AbstractOrderShipmentStatus {
            public function support(string $status): bool
            {
                return true;
            }

            public function new(OrderShipment $orderShipment): void
            {
                $orderShipment->setStatus(OrderShipmentStatus::NEW);
            }

            public function validTransitions(): array
            {
                return [
                    OrderShipmentStatus::NEW,
                    OrderShipmentStatus::CANCELED,
                    OrderShipmentStatus::WAREHOUSE,
                ];
            }
        };

        $factory = Mockery::mock(OrderShipmentStatusFactory::class);
        $factory->shouldReceive('create')
                ->once()
                ->with(OrderShipmentStatus::WAITING_FOR_SEND)
                ->andReturn($statusObject);

        $manager = Mockery::mock(EntityManagerInterface::class);
        $manager->shouldReceive('beginTransaction')->once()->withNoArgs()->andReturn();
        $manager->shouldReceive('flush')->once()->withNoArgs()->andReturn();
        $manager->shouldReceive('commit')->once()->withNoArgs()->andReturn();

        $admin         = new Admin();
        $orderMock     = Mockery::mock(Order::class);
        $orderShipment = new OrderShipment();
        $orderShipment->setStatus(OrderShipmentStatus::WAITING_FOR_SEND)
                      ->setOrder($orderMock);

        $logCreator = Mockery::mock(OrderShipmentStatusLogCreator::class);
        $logCreator->shouldReceive('create')
                   ->once()
                   ->with(
                       $orderShipment,
                       OrderShipmentStatus::WAITING_FOR_SEND,
                       OrderShipmentStatus::NEW,
                       $admin
                   )
                   ->andReturn();

        $dispatcher = Mockery::mock(EventDispatcherInterface::class);

        $dispatcher->shouldReceive('dispatch')
                   ->once()
                   ->with(Mockery::type(OrderShipmentStatusChanged::class));

        $managerRegistry = Mockery::mock(ManagerRegistry::class);

        $service = new OrderShipmentStatusService($manager, $factory, $dispatcher, $logCreator, $managerRegistry);

        $service->change($orderShipment, OrderShipmentStatus::NEW, $admin);
    }

    public function testItCanChangeOrderShipmentStatusToPackaged(): void
    {
        $orderStatusService       = Mockery::mock(OrderStatusService::class);
        $sellerOrderStatusService = Mockery::mock(SellerOrderItemStatusService::class);
        $recalculateDocumentMock  = Mockery::mock(RecalculateOrderDocument::class);

        $args = [$orderStatusService, $sellerOrderStatusService, $recalculateDocumentMock];

        $statusObject = new class (...$args) extends AbstractOrderShipmentStatus {
            public function support(string $status): bool
            {
                return true;
            }

            public function packaged(OrderShipment $orderShipment): void
            {
                $orderShipment->setStatus(OrderShipmentStatus::PACKAGED);
            }

            public function validTransitions(): array
            {
                return [
                    OrderShipmentStatus::PACKAGED,
                    OrderShipmentStatus::CANCELED,
                    OrderShipmentStatus::WAREHOUSE,
                ];
            }
        };

        $factory = Mockery::mock(OrderShipmentStatusFactory::class);
        $factory->shouldReceive('create')
                ->once()
                ->with(OrderShipmentStatus::PACKAGED)
                ->andReturn($statusObject);

        $manager = Mockery::mock(EntityManagerInterface::class);
        $manager->shouldReceive('beginTransaction')->once()->withNoArgs()->andReturn();
        $manager->shouldReceive('flush')->once()->withNoArgs()->andReturn();
        $manager->shouldReceive('commit')->once()->withNoArgs()->andReturn();

        $admin         = new Admin();
        $orderMock     = Mockery::mock(Order::class);
        $orderShipment = new OrderShipment();
        $orderShipment->setStatus(OrderShipmentStatus::PACKAGED)
                      ->setOrder($orderMock);

        $logCreator = Mockery::mock(OrderShipmentStatusLogCreator::class);
        $logCreator->shouldReceive('create')
                   ->once()
                   ->with(
                       $orderShipment,
                       OrderShipmentStatus::PACKAGED,
                       OrderShipmentStatus::PACKAGED,
                       $admin
                   )
                   ->andReturn();

        $dispatcher = Mockery::mock(EventDispatcherInterface::class);

        $dispatcher->shouldReceive('dispatch')
                   ->once()
                   ->with(Mockery::type(OrderShipmentStatusChanged::class));

        $managerRegistry = Mockery::mock(ManagerRegistry::class);

        $service = new OrderShipmentStatusService($manager, $factory, $dispatcher, $logCreator, $managerRegistry);

        $service->change($orderShipment, OrderShipmentStatus::PACKAGED, $admin);
    }

    public function testItRollbackTransactionIfExceptionOccur(): void
    {
        $orderStatusService       = Mockery::mock(OrderStatusService::class);
        $sellerOrderStatusService = Mockery::mock(SellerOrderItemStatusService::class);
        $recalculateDocumentMock  = Mockery::mock(RecalculateOrderDocument::class);

        $args = [$orderStatusService, $sellerOrderStatusService, $recalculateDocumentMock];

        $statusObject = new class (...$args) extends AbstractOrderShipmentStatus {
            public function support(string $status): bool
            {
                return true;
            }

            public function new(OrderShipment $orderShipment): void
            {
                throw new Exception();
            }

            public function validTransitions(): array
            {
                return [];
            }
        };

        $factory = Mockery::mock(OrderShipmentStatusFactory::class);
        $factory->shouldReceive('create')
                ->once()
                ->with(OrderShipmentStatus::WAITING_FOR_SEND)
                ->andReturn($statusObject);

        $manager = Mockery::mock(EntityManagerInterface::class);
        $manager->shouldReceive('beginTransaction')->once()->withNoArgs()->andReturn();
        $manager->shouldReceive('close')->once()->withNoArgs()->andReturn();
        $manager->shouldReceive('rollback')->once()->withNoArgs()->andReturn();

        $admin         = new Admin();
        $orderShipment = new OrderShipment();
        $orderShipment->setStatus(OrderShipmentStatus::WAITING_FOR_SEND);

        $logCreator = Mockery::mock(OrderShipmentStatusLogCreator::class);
        $logCreator->shouldNotReceive('create');

        $managerRegistry = Mockery::mock(ManagerRegistry::class);

        $service = new OrderShipmentStatusService(
            $manager,
            $factory,
            Mockery::mock(EventDispatcherInterface::class),
            $logCreator,
            $managerRegistry
        );

        $this->expectException(Exception::class);

        $service->change($orderShipment, OrderShipmentStatus::NEW, $admin);
    }

    public function testItThrowsExceptionAndRollbackWhenOneTimeRetryableExceptionOccur(): void
    {
        $orderStatusService       = Mockery::mock(OrderStatusService::class);
        $sellerOrderStatusService = Mockery::mock(SellerOrderItemStatusService::class);
        $recalculateDocumentMock  = Mockery::mock(RecalculateOrderDocument::class);

        $args = [$orderStatusService, $sellerOrderStatusService, $recalculateDocumentMock];

        $statusObject = new class (...$args) extends AbstractOrderShipmentStatus {
            public function support(string $status): bool
            {
                return true;
            }

            public function new(OrderShipment $orderShipment): void
            {
                $orderShipment->setStatus(OrderShipmentStatus::NEW);
            }

            public function validTransitions(): array
            {
                return [
                    OrderShipmentStatus::NEW,
                    OrderShipmentStatus::CANCELED,
                    OrderShipmentStatus::WAREHOUSE,
                ];
            }
        };

        $factory = Mockery::mock(OrderShipmentStatusFactory::class);
        $factory->shouldReceive('create')
                ->once()
                ->with(OrderShipmentStatus::WAITING_FOR_SEND)
                ->andReturn($statusObject);

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

        $admin         = new Admin();
        $orderMock     = Mockery::mock(Order::class);
        $orderShipment = new OrderShipment();
        $orderShipment->setStatus(OrderShipmentStatus::WAITING_FOR_SEND)
                      ->setOrder($orderMock);

        $logCreator = Mockery::mock(OrderShipmentStatusLogCreator::class);
        $logCreator->shouldReceive('create')
                   ->times(3)
                   ->with(
                       $orderShipment,
                       OrderShipmentStatus::WAITING_FOR_SEND,
                       OrderShipmentStatus::NEW,
                       $admin
                   )
                   ->andReturn();

        $dispatcher      = Mockery::mock(EventDispatcherInterface::class);
        $managerRegistry = Mockery::mock(ManagerRegistry::class);

        $managerRegistry->shouldReceive('resetManager')
                        ->times(3)
                        ->withNoArgs()
                        ->andReturn();

        $this->expectException(DeadlockException::class);

        $service = new OrderShipmentStatusService($manager, $factory, $dispatcher, $logCreator, $managerRegistry);

        $service->change($orderShipment, OrderShipmentStatus::NEW, $admin);
    }
}
