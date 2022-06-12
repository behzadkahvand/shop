<?php

namespace App\Tests\Unit\Command\Job;

use App\Command\Job\CancelUnpaidOrdersCommand;
use App\Dictionary\OrderShipmentStatus;
use App\Dictionary\OrderStatus;
use App\Dictionary\TransferReason;
use App\Entity\Order;
use App\Events\Order\OrderBalanceAmountEvent;
use App\Repository\OrderRepository;
use App\Service\Order\Wallet\OrderWalletPaymentHandler;
use App\Service\OrderShipment\SystemChangeOrderShipmentStatus\SystemChangeOrderShipmentStatus;
use App\Service\OrderStatusLog\CreateOrderStatusLogService;
use App\Service\OrderStatusLog\ValueObjects\CreateOrderStatusLogValueObject;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Psr\Log\LoggerInterface;
use stdClass;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CancelUnpaidOrdersCommandTest extends MockeryTestCase
{
    protected LegacyMockInterface|CreateOrderStatusLogService|MockInterface|null $createOrderStatusLogService;

    protected LegacyMockInterface|SystemChangeOrderShipmentStatus|MockInterface|null $changeOrderShipmentStatus;

    protected LegacyMockInterface|MockInterface|OrderRepository|null $orderRepoMock;

    protected LegacyMockInterface|EntityManagerInterface|MockInterface|null $entityManager;

    protected LegacyMockInterface|EventDispatcherInterface|MockInterface|null $eventDispatcher;

    private CommandTester $commandTester;
    /**
     * @var \Doctrine\Persistence\ManagerRegistry|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private ManagerRegistry|LegacyMockInterface|MockInterface|null $registery;
    /**
     * @var \Mockery\LegacyMockInterface|\Mockery\MockInterface|\Psr\Log\LoggerInterface
     */
    private LoggerInterface|LegacyMockInterface|MockInterface|null $logger;
    /**
     * @var \App\Service\Order\Wallet\OrderWalletPaymentHandler|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private LegacyMockInterface|MockInterface|OrderWalletPaymentHandler|null $walletPaymentHandler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->entityManager = Mockery::mock(EntityManagerInterface::class);
        $this->entityManager->shouldReceive('beginTransaction')->once()->withNoArgs()->andReturn();

        $this->orderRepoMock               = Mockery::mock(OrderRepository::class);
        $this->createOrderStatusLogService = Mockery::mock(CreateOrderStatusLogService::class);
        $this->changeOrderShipmentStatus   = Mockery::mock(SystemChangeOrderShipmentStatus::class);
        $this->eventDispatcher             = Mockery::mock(EventDispatcherInterface::class);
        $this->registery                   = Mockery::mock(ManagerRegistry::class);
        $this->walletPaymentHandler = Mockery::mock(OrderWalletPaymentHandler::class);
        $application = new Application();
        $this->logger = Mockery::mock(LoggerInterface::class);
        $command = new CancelUnpaidOrdersCommand(
            $this->entityManager,
            $this->orderRepoMock,
            $this->createOrderStatusLogService,
            $this->changeOrderShipmentStatus,
            $this->eventDispatcher,
            $this->registery,
            $this->walletPaymentHandler
        );
        $command->setLogger($this->logger);
        $application->add($command);

        $command             = $application->find('timcheh:job:cancel-unpaid-orders');
        $this->commandTester = new CommandTester($command);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->commandTester);

        $this->entityManager               = null;
        $this->orderRepoMock               = null;
        $this->createOrderStatusLogService = null;
        $this->changeOrderShipmentStatus   = null;
        $this->eventDispatcher             = null;
        $this->registery             = null;

        Mockery::close();
    }

    public function testExecute(): void
    {
        $order        = new Order();
        $order->setId(1);
        $unpaidOrders = [$order];

        $this->entityManager->shouldReceive('flush')->once()->withNoArgs()->andReturn();
        $this->entityManager->shouldReceive('commit')->once()->withNoArgs()->andReturn();
        $this->entityManager->shouldReceive('contains')->once()->with($order)->andReturnTrue();

        $this->orderRepoMock->shouldReceive('findAllUnpaidOrdersAfterOneHour')
                         ->andReturn($unpaidOrders);

        $this->createOrderStatusLogService->shouldReceive('perform')
                                          ->with(Mockery::type(CreateOrderStatusLogValueObject::class), false)
                                          ->andReturn();

        $this->changeOrderShipmentStatus->shouldReceive('change')
                                        ->with($unpaidOrders[0], OrderShipmentStatus::CANCELED)
                                        ->andReturn();

        $this->eventDispatcher->shouldReceive('dispatch')->once()
                              ->with(Mockery::type(OrderBalanceAmountEvent::class))
                              ->andReturn(new stdClass());

        $this->walletPaymentHandler
            ->shouldReceive('handle')
            ->once()
            ->with($order, TransferReason::ORDER_CANCELED_BY_SYSTEM)
            ->andReturnNull();

        $this->commandTester->execute([]);

        self::assertEquals(OrderStatus::CANCELED_SYSTEM, $unpaidOrders[0]->getStatus());

        $orderShipments = $unpaidOrders[0]->getShipments();
        foreach ($orderShipments as $orderShipment) {
            self::assertEquals(OrderShipmentStatus::CANCELED, $orderShipment->getStatus());
        }
    }

    public function testExecuteWithMock(): void
    {
        $order        = Mockery::mock(Order::class);
        $unpaidOrders = [$order];

        $order->shouldReceive('releaseReservedStock')
              ->once()
              ->withNoArgs()
              ->andReturn();
        $order->shouldReceive('setStatus')
              ->once()
              ->with(OrderStatus::CANCELED_SYSTEM)
              ->andReturn($order);
        $order->shouldReceive('getId')
              ->twice()
              ->withNoArgs()
              ->andReturn(1);

        $this->eventDispatcher->shouldReceive('dispatch')->once()
                              ->with(Mockery::type(OrderBalanceAmountEvent::class))
                              ->andReturn(new stdClass());

        $this->entityManager->shouldReceive('flush')->once()->withNoArgs()->andReturn();
        $this->entityManager->shouldReceive('commit')->once()->withNoArgs()->andReturn();
        $this->entityManager->shouldReceive('contains')->once()->with($order)->andReturnTrue();

        $this->orderRepoMock->shouldReceive('findAllUnpaidOrdersAfterOneHour')
                         ->andReturn($unpaidOrders);

        $this->createOrderStatusLogService->shouldReceive('perform')
                                          ->with(Mockery::type(CreateOrderStatusLogValueObject::class), false)
                                          ->andReturn();

        $this->changeOrderShipmentStatus->shouldReceive('change')
                                        ->with($unpaidOrders[0], OrderShipmentStatus::CANCELED)
                                        ->andReturn();

        $this->walletPaymentHandler
            ->shouldReceive('handle')
            ->once()
            ->with($order, TransferReason::ORDER_CANCELED_BY_SYSTEM)
            ->andReturnNull();


        $this->commandTester->execute([]);
    }

    public function testExecuteFail(): void
    {
        $unpaidOrders = [new Order()];
        $this->entityManager->shouldReceive('close')->once()->withNoArgs()->andReturn();
        $this->entityManager->shouldReceive('rollback')->once()->withNoArgs()->andReturn();
        $this->entityManager->shouldReceive('contains')->once()->with($unpaidOrders[0])->andReturnTrue();
        $this->registery->shouldReceive('resetManager')->once()->withNoArgs()->andReturnNull();
        $this->logger->shouldReceive('error')->once()->andReturnNull();

        $this->orderRepoMock->shouldReceive('findAllUnpaidOrdersAfterOneHour')
                         ->andReturn($unpaidOrders);

        $this->createOrderStatusLogService->shouldReceive('perform')
                                          ->with(Mockery::type(CreateOrderStatusLogValueObject::class), false)
                                          ->andReturn();

        $this->changeOrderShipmentStatus->shouldReceive('change')
                                        ->with($unpaidOrders[0], OrderShipmentStatus::CANCELED)
                                        ->andThrow(Exception::class);

        $this->commandTester->execute([]);
    }
}
