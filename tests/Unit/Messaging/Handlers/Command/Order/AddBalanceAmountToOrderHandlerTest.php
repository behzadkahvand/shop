<?php

namespace App\Tests\Unit\Messaging\Handlers\Command\Order;

use App\Dictionary\OrderBalanceStatus;
use App\Entity\Order;
use App\Messaging\Handlers\Command\Order\AddBalanceAmountToOrderHandler;
use App\Messaging\Messages\Command\Order\AddBalanceAmountToOrder;
use App\Service\Order\OrderBalanceStatus\OrderBalanceStatusService;
use App\Tests\Unit\BaseUnitTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Psr\Log\LoggerInterface;

class AddBalanceAmountToOrderHandlerTest extends BaseUnitTestCase
{
    protected LegacyMockInterface|MockInterface|OrderBalanceStatusService|null $balanceStatusMock;

    protected LegacyMockInterface|EntityManagerInterface|MockInterface|null $em;

    protected LoggerInterface|LegacyMockInterface|MockInterface|null $loggerMock;

    protected Order|LegacyMockInterface|MockInterface|null $orderMock;

    protected ?AddBalanceAmountToOrderHandler $addBalanceAmountToOrderHandler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->balanceStatusMock = Mockery::mock(OrderBalanceStatusService::class);
        $this->em                = Mockery::mock(EntityManagerInterface::class);
        $this->loggerMock        = Mockery::mock(LoggerInterface::class);
        $this->orderMock         = Mockery::mock(Order::class);

        $this->addBalanceAmountToOrderHandler = new AddBalanceAmountToOrderHandler(
            $this->balanceStatusMock,
            $this->em
        );
    }

    public function testItDoNothingWhenOrderNotFound(): void
    {
        $orderId = 59;

        $addBalanceAmountToOrder = new AddBalanceAmountToOrder($orderId);

        $this->em->shouldReceive('getReference')
                 ->once()
                 ->with(Order::class, $orderId)
                 ->andReturnNull();

        $this->addBalanceAmountToOrderHandler->setLogger($this->loggerMock);

        $this->loggerMock->shouldReceive('error')
                         ->once()
                         ->with(sprintf('It can not add balance amount to order %d when order not exist!', $orderId))
                         ->andReturn();

        $this->addBalanceAmountToOrderHandler->__invoke($addBalanceAmountToOrder);
    }

    public function testItCanAddBalanceAmountToOrderWhenOrderBalanceStatusEqualsToBalance(): void
    {
        $orderId = 59;

        $addBalanceAmountToOrder = new AddBalanceAmountToOrder($orderId);

        $this->em->shouldReceive('getReference')
                 ->once()
                 ->with(Order::class, $orderId)
                 ->andReturn($this->orderMock);

        $this->balanceStatusMock->shouldReceive('get')
                                ->once()
                                ->with($orderId)
                                ->andReturn([
                                    'balanceStatus' => OrderBalanceStatus::BALANCE,
                                    'balanceAmount' => 0
                                ]);

        $this->orderMock->shouldReceive('setBalanceAmount')
                        ->once()
                        ->with(0)
                        ->andReturn($this->orderMock);

        $this->em->shouldReceive('flush')
                 ->once()
                 ->withNoArgs()
                 ->andReturn();

        $this->addBalanceAmountToOrderHandler->__invoke($addBalanceAmountToOrder);
    }

    public function testItCanAddBalanceAmountToOrderWhenOrderBalanceStatusEqualsToCreditor(): void
    {
        $orderId = 59;

        $addBalanceAmountToOrder = new AddBalanceAmountToOrder($orderId);

        $this->em->shouldReceive('getReference')
                 ->once()
                 ->with(Order::class, $orderId)
                 ->andReturn($this->orderMock);

        $this->balanceStatusMock->shouldReceive('get')
                                ->once()
                                ->with($orderId)
                                ->andReturn([
                                    'balanceStatus' => OrderBalanceStatus::CREDITOR,
                                    'balanceAmount' => 100000
                                ]);

        $this->orderMock->shouldReceive('setBalanceAmount')
                        ->once()
                        ->with(100000)
                        ->andReturn($this->orderMock);

        $this->em->shouldReceive('flush')
                 ->once()
                 ->withNoArgs()
                 ->andReturn();

        $this->addBalanceAmountToOrderHandler->__invoke($addBalanceAmountToOrder);
    }

    public function testItCanAddBalanceAmountToOrderWhenOrderBalanceStatusEqualsToDebtor(): void
    {
        $orderId = 59;

        $addBalanceAmountToOrder = new AddBalanceAmountToOrder($orderId);

        $this->em->shouldReceive('getReference')
                 ->once()
                 ->with(Order::class, $orderId)
                 ->andReturn($this->orderMock);

        $this->balanceStatusMock->shouldReceive('get')
                                ->once()
                                ->with($orderId)
                                ->andReturn([
                                    'balanceStatus' => OrderBalanceStatus::DEBTOR,
                                    'balanceAmount' => 1000000
                                ]);

        $this->orderMock->shouldReceive('setBalanceAmount')
                        ->once()
                        ->with(-1000000)
                        ->andReturn($this->orderMock);

        $this->em->shouldReceive('flush')
                 ->once()
                 ->withNoArgs()
                 ->andReturn();

        $this->addBalanceAmountToOrderHandler->__invoke($addBalanceAmountToOrder);
    }
}
