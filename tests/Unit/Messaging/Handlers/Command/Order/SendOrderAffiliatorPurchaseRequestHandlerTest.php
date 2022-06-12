<?php

namespace App\Tests\Unit\Messaging\Handlers\Command\Order;

use App\Entity\Order;
use App\Messaging\Handlers\Command\Order\SendOrderAffiliatorPurchaseRequestHandler;
use App\Messaging\Messages\Command\Order\SendOrderAffiliatorPurchaseRequest;
use App\Repository\OrderRepository;
use App\Service\OrderAffiliator\OrderAffiliatorService;
use App\Tests\Unit\BaseUnitTestCase;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Psr\Log\LoggerInterface;

class SendOrderAffiliatorPurchaseRequestHandlerTest extends BaseUnitTestCase
{
    protected LegacyMockInterface|MockInterface|OrderRepository|null $orderRepoMock;

    protected OrderAffiliatorService|LegacyMockInterface|MockInterface|null $orderAffiliatorServiceMock;

    protected Order|LegacyMockInterface|MockInterface|null $orderMock;

    protected LoggerInterface|LegacyMockInterface|MockInterface|null $loggerMock;

    protected ?SendOrderAffiliatorPurchaseRequestHandler $sendAffiliatorRequestHandler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderRepoMock              = Mockery::mock(OrderRepository::class);
        $this->orderAffiliatorServiceMock = Mockery::mock(OrderAffiliatorService::class);
        $this->orderMock                  = Mockery::mock(Order::class);
        $this->loggerMock                 = Mockery::mock(LoggerInterface::class);

        $this->sendAffiliatorRequestHandler = new SendOrderAffiliatorPurchaseRequestHandler(
            $this->orderRepoMock,
            $this->orderAffiliatorServiceMock
        );

        $this->sendAffiliatorRequestHandler->setLogger($this->loggerMock);
    }

    public function testItDoNothingWhenOrderNotFound(): void
    {
        $orderId         = 43;
        $purchaseRequest = new SendOrderAffiliatorPurchaseRequest($orderId);

        $this->loggerMock->expects('debug')->with('affiliator handler')->andReturnNull();

        $this->orderRepoMock->shouldReceive('find')
                            ->once()
                            ->with($orderId)
                            ->andReturnNull();

        $this->loggerMock->shouldReceive('error')
                         ->once()
                         ->with(sprintf('It can not send affiliator purchase request for order %d', $orderId))
                         ->andReturn();

        $this->sendAffiliatorRequestHandler->__invoke($purchaseRequest);
    }

    public function testItCanSendAffiliatorPurchaseRequest(): void
    {
        $orderId         = 43;
        $purchaseRequest = new SendOrderAffiliatorPurchaseRequest($orderId);

        $this->loggerMock->expects('debug')->with('affiliator handler')->andReturnNull();

        $this->orderRepoMock->shouldReceive('find')
                            ->once()
                            ->with($orderId)
                            ->andReturn($this->orderMock);

        $this->orderAffiliatorServiceMock->shouldReceive('purchase')
                                         ->once()
                                         ->with($this->orderMock)
                                         ->andReturn();

        $this->sendAffiliatorRequestHandler->__invoke($purchaseRequest);
    }
}
