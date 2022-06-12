<?php

namespace App\Tests\Unit\Service\Order\OrderStatus;

use App\Dictionary\OrderStatus;
use App\Entity\Admin;
use App\Entity\Order;
use App\Service\Order\OrderStatus\Events\OrderStatusChanged;
use App\Service\Order\OrderStatus\Exceptions\InvalidOrderStatusMethodException;
use App\Service\Order\OrderStatus\Exceptions\InvalidOrderStatusTransitionException;
use App\Service\Order\OrderStatus\OrderStatusFactory;
use App\Service\Order\OrderStatus\OrderStatusService;
use App\Service\Order\OrderStatus\WaitCustomerOrderStatus;
use App\Service\OrderStatusLog\CreateOrderStatusLogService;
use App\Service\OrderStatusLog\ValueObjects\CreateOrderStatusLogValueObject;
use Doctrine\DBAL\Driver\Mysqli\Exception\ConnectionFailed;
use Doctrine\DBAL\Exception\DeadlockException;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class OrderStatusServiceTest extends MockeryTestCase
{
    /**
     * @var EntityManager|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $em;

    /**
     * @var OrderStatusFactory|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $factoryMock;

    /**
     * @var CreateOrderStatusLogService|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $createStatusLogMock;

    /**
     * @var Order|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $orderMock;

    /**
     * @var Admin|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $adminMock;

    /**
     * @var WaitCustomerOrderStatus|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $waitCustomerStatusMock;

    /**
     * @var CreateOrderStatusLogValueObject|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $logValueObjMock;

    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|EventDispatcherInterface
     */
    private $dispatcherMock;

    /**
     * @var ManagerRegistry|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    private $managerRegistryMock;

    protected OrderStatusService $orderStatusService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->em                     = Mockery::mock(EntityManager::class);
        $this->factoryMock            = Mockery::mock(OrderStatusFactory::class);
        $this->createStatusLogMock    = Mockery::mock(CreateOrderStatusLogService::class);
        $this->orderMock              = Mockery::mock(Order::class);
        $this->adminMock              = Mockery::mock(Admin::class);
        $this->waitCustomerStatusMock = Mockery::mock(WaitCustomerOrderStatus::class);
        $this->logValueObjMock        = Mockery::mock(CreateOrderStatusLogValueObject::class);
        $this->dispatcherMock         = Mockery::mock(EventDispatcherInterface::class);
        $this->managerRegistryMock    = Mockery::mock(ManagerRegistry::class);

        $this->orderStatusService = new OrderStatusService(
            $this->em,
            $this->factoryMock,
            $this->createStatusLogMock,
            $this->dispatcherMock,
            $this->managerRegistryMock
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->em,
            $this->factoryMock,
            $this->createStatusLogMock,
            $this->orderMock,
            $this->adminMock,
            $this->waitCustomerStatusMock,
            $this->logValueObjMock,
            $this->orderStatusService,
            $this->managerRegistryMock
        );
    }

    public function testItThrowsExceptionWhenMethodDoesNotExist(): void
    {
        $this->orderMock->shouldReceive('getStatus')
                        ->once()
                        ->withNoArgs()
                        ->andReturn(OrderStatus::WAIT_CUSTOMER);

        $this->factoryMock->shouldReceive('create')
                          ->once()
                          ->with(OrderStatus::WAIT_CUSTOMER)
                          ->andReturn($this->waitCustomerStatusMock);

        $this->expectException(InvalidOrderStatusMethodException::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage('Order status method is invalid!');

        $this->orderStatusService->change($this->orderMock, 'INVALID');
    }

    public function testItCanChangeOrderStatus(): void
    {
        $this->orderMock->shouldReceive('getStatus')
                        ->once()
                        ->withNoArgs()
                        ->andReturn(OrderStatus::WAIT_CUSTOMER);

        $this->factoryMock->shouldReceive('create')
                          ->once()
                          ->with(OrderStatus::WAIT_CUSTOMER)
                          ->andReturn($this->waitCustomerStatusMock);
        $this->factoryMock->shouldReceive('getCreateOrderStatusLogValueObject')
                          ->once()
                          ->withNoArgs()
                          ->andReturn($this->logValueObjMock);

        $this->em->shouldReceive('beginTransaction')->once()->withNoArgs()->andReturn();
        $this->em->shouldReceive('flush')->once()->withNoArgs()->andReturn();
        $this->em->shouldReceive('commit')->once()->withNoArgs()->andReturn();

        $this->waitCustomerStatusMock->shouldReceive('callFailed')
                                     ->once()
                                     ->with($this->orderMock)
                                     ->andReturn();

        $this->logValueObjMock->shouldReceive('setOrder')
                              ->once()
                              ->with($this->orderMock)
                              ->andReturn($this->logValueObjMock);
        $this->logValueObjMock->shouldReceive('setStatusFrom')
                              ->once()
                              ->with(OrderStatus::WAIT_CUSTOMER)
                              ->andReturn($this->logValueObjMock);
        $this->logValueObjMock->shouldReceive('setStatusTo')
                              ->once()
                              ->with(OrderStatus::CALL_FAILED)
                              ->andReturn($this->logValueObjMock);
        $this->logValueObjMock->shouldReceive('setUser')
                              ->once()
                              ->with(null)
                              ->andReturn($this->logValueObjMock);

        $this->createStatusLogMock->shouldReceive('perform')
                                  ->once()
                                  ->with($this->logValueObjMock)
                                  ->andReturn();

        $this->dispatcherMock->shouldReceive('dispatch')
                             ->once()
                             ->with(Mockery::type(OrderStatusChanged::class));

        $this->orderStatusService->change($this->orderMock, OrderStatus::CALL_FAILED);
    }

    public function testItThrowsExceptionAndRollbackWhenChangeOrderStatusIsFailed(): void
    {
        $this->orderMock->shouldReceive('getStatus')
                        ->once()
                        ->withNoArgs()
                        ->andReturn(OrderStatus::WAIT_CUSTOMER);

        $this->factoryMock->shouldReceive('create')
                          ->once()
                          ->with(OrderStatus::WAIT_CUSTOMER)
                          ->andReturn($this->waitCustomerStatusMock);

        $this->em->shouldReceive('beginTransaction')->once()->withNoArgs()->andReturn();
        $this->em->shouldReceive('rollback')->once()->withNoArgs()->andReturn();
        $this->em->shouldReceive('close')->once()->withNoArgs()->andReturn();
        $this->em->shouldNotReceive('commit');

        $this->waitCustomerStatusMock->shouldReceive('new')
                                     ->once()
                                     ->with($this->orderMock)
                                     ->andThrows(new InvalidOrderStatusTransitionException());

        $this->expectException(InvalidOrderStatusTransitionException::class);
        $this->expectExceptionCode(422);
        $this->expectExceptionMessage('Order status transition is invalid!');

        $this->orderStatusService->change($this->orderMock, OrderStatus::NEW);
    }

    public function testItThrowsExceptionAndRollbackWhenOneTimeRetryableExceptionOccur(): void
    {
        $this->orderMock->shouldReceive('getStatus')
                        ->once()
                        ->withNoArgs()
                        ->andReturn(OrderStatus::WAIT_CUSTOMER);

        $this->factoryMock->shouldReceive('create')
                          ->once()
                          ->with(OrderStatus::WAIT_CUSTOMER)
                          ->andReturn($this->waitCustomerStatusMock);

        $this->em->shouldReceive('beginTransaction')->times(3)->withNoArgs()->andReturn();
        $this->em->shouldReceive('flush')
                 ->times(3)
                 ->withNoArgs()
                 ->andThrow(new DeadlockException('', new class () extends ConnectionFailed {
                    public function __construct()
                    {
                        parent::__construct('', null, null);
                    }
                 }));

        $this->em->shouldReceive('rollback')->times(3)->withNoArgs()->andReturn();
        $this->em->shouldReceive('close')->times(3)->withNoArgs()->andReturn();
        $this->em->shouldNotReceive('commit');

        $this->waitCustomerStatusMock->shouldReceive('callFailed')
                                     ->times(3)
                                     ->with($this->orderMock)
                                     ->andReturn();

        $this->managerRegistryMock->shouldReceive('resetManager')
                                  ->times(3)
                                  ->withNoArgs()
                                  ->andReturn();

        $this->expectException(DeadlockException::class);

        $this->orderStatusService->change($this->orderMock, OrderStatus::CALL_FAILED);
    }
}
