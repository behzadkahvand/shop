<?php

namespace App\Tests\Unit\Service\OrderShipment\PartialOrderShipmentTransaction;

use App\Dictionary\TransactionStatus;
use App\DTO\Admin\PartialOrderShipmentTransactionData;
use App\Entity\Order;
use App\Entity\OrderDocument;
use App\Entity\OrderShipment;
use App\Entity\Transaction;
use App\Events\Order\OrderBalanceAmountEvent;
use App\Repository\OrderShipmentRepository;
use App\Service\OrderShipment\PartialOrderShipmentTransaction\Exceptions\OrderShipmentNotFoundException;
use App\Service\OrderShipment\PartialOrderShipmentTransaction\PartialOrderShipmentTransactionFactory;
use App\Service\OrderShipment\PartialOrderShipmentTransaction\PartialOrderShipmentTransactionService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PartialOrderShipmentTransactionServiceTest extends MockeryTestCase
{
    /**
     * @var OrderShipmentRepository|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $orderShipmentRepoMock;

    /**
     * @var PartialOrderShipmentTransactionFactory|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $factoryMock;

    /**
     * @var EntityManagerInterface|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $em;

    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|EventDispatcherInterface
     */
    protected $dispatcherMock;

    /**
     * @var OrderShipment|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $orderShipmentMock;

    /**
     * @var Order|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $orderMock;

    /**
     * @var OrderDocument|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $orderDocumentMock;

    /**
     * @var Transaction|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $transactionMock;

    protected PartialOrderShipmentTransactionData $transactionData;

    protected PartialOrderShipmentTransactionService $shipmentTransactionService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderShipmentRepoMock = Mockery::mock(OrderShipmentRepository::class);
        $this->factoryMock           = Mockery::mock(PartialOrderShipmentTransactionFactory::class);
        $this->em                    = Mockery::mock(EntityManagerInterface::class);
        $this->dispatcherMock        = Mockery::mock(EventDispatcherInterface::class);
        $this->orderShipmentMock     = Mockery::mock(OrderShipment::class);
        $this->orderMock             = Mockery::mock(Order::class);
        $this->orderDocumentMock     = Mockery::mock(OrderDocument::class);
        $this->transactionMock       = Mockery::mock(Transaction::class);

        $this->transactionData = new PartialOrderShipmentTransactionData();
        $this->transactionData->setTrackingNumber('tracking-number-code')
                              ->setPaidAt(new DateTimeImmutable('now'));

        $this->shipmentTransactionService = new PartialOrderShipmentTransactionService(
            $this->orderShipmentRepoMock,
            $this->factoryMock,
            $this->em,
            $this->dispatcherMock
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->transactionData,
            $this->shipmentTransactionService
        );

        $this->orderShipmentRepoMock = null;
        $this->factoryMock           = null;
        $this->em                    = null;
        $this->dispatcherMock        = null;
        $this->orderShipmentMock     = null;
        $this->orderMock             = null;
        $this->orderDocumentMock     = null;
        $this->transactionMock       = null;
    }

    public function testItThrowsExceptionWhenOrderShipmentIsNotFound(): void
    {
        $this->em->shouldReceive('beginTransaction')
                 ->once()
                 ->withNoArgs()
                 ->andReturnNull();
        $this->em->shouldReceive('close')
                 ->once()
                 ->withNoArgs()
                 ->andReturnNull();
        $this->em->shouldReceive('rollback')
                 ->once()
                 ->withNoArgs()
                 ->andReturnNull();

        $this->orderShipmentRepoMock->shouldReceive('findShipmentForCreateTransaction')
                                    ->once()
                                    ->with(1)
                                    ->andReturnNull();

        $this->expectException(OrderShipmentNotFoundException::class);
        $this->expectExceptionCode(404);
        $this->expectExceptionMessage('Order shipment is not found for create transaction action!');

        $this->shipmentTransactionService->create(1, $this->transactionData);
    }

    public function testItCanCreateShipmentTransaction(): void
    {
        $this->em->shouldReceive('beginTransaction')
                 ->once()
                 ->withNoArgs()
                 ->andReturnNull();

        $this->orderShipmentRepoMock->shouldReceive('findShipmentForCreateTransaction')
                                    ->once()
                                    ->with(1)
                                    ->andReturn($this->orderShipmentMock);

        $this->orderShipmentMock->shouldReceive('getOrder')
                                ->once()
                                ->withNoArgs()
                                ->andReturn($this->orderMock);
        $this->orderShipmentMock->shouldReceive('getGrandTotal')
                                ->once()
                                ->withNoArgs()
                                ->andReturn(45000);
        $this->orderShipmentMock->shouldReceive('getTotalOrderItemPrices')
                                ->once()
                                ->withNoArgs()
                                ->andReturn(550000);
        $this->orderShipmentMock->shouldReceive('setTransaction')
                                ->once()
                                ->with($this->transactionMock)
                                ->andReturn($this->orderShipmentMock);

        $this->orderMock->shouldReceive('getOrderDocument')
                        ->once()
                        ->withNoArgs()
                        ->andReturn($this->orderDocumentMock);
        $this->orderMock->shouldReceive('getId')
                        ->once()
                        ->withNoArgs()
                        ->andReturn(59);

        $this->factoryMock->shouldReceive('createTransaction')
                          ->once()
                          ->withNoArgs()
                          ->andReturn($this->transactionMock);

        $this->transactionMock->shouldReceive('setAmount')
                              ->once()
                              ->with(595000)
                              ->andReturn($this->transactionMock);
        $this->transactionMock->shouldReceive('setDocument')
                              ->once()
                              ->with($this->orderDocumentMock)
                              ->andReturn($this->transactionMock);
        $this->transactionMock->shouldReceive('setGateway')
                              ->once()
                              ->with(PartialOrderShipmentTransactionService::GATEWAY_NAME)
                              ->andReturn($this->transactionMock);
        $this->transactionMock->shouldReceive('setPaidAt')
                              ->once()
                              ->with($this->transactionData->getPaidAt())
                              ->andReturn($this->transactionMock);
        $this->transactionMock->shouldReceive('setTrackingNumber')
                              ->once()
                              ->with($this->transactionData->getTrackingNumber())
                              ->andReturn($this->transactionMock);
        $this->transactionMock->shouldReceive('setStatus')
                              ->once()
                              ->with(TransactionStatus::SUCCESS)
                              ->andReturn($this->transactionMock);

        $this->dispatcherMock->shouldReceive('dispatch')
                             ->once()
                             ->with(Mockery::type(OrderBalanceAmountEvent::class));

        $this->em->shouldReceive('persist')
                 ->once()
                 ->with($this->transactionMock)
                 ->andReturnNull();
        $this->em->shouldReceive('flush')
                 ->once()
                 ->withNoArgs()
                 ->andReturnNull();
        $this->em->shouldReceive('commit')
                 ->once()
                 ->withNoArgs()
                 ->andReturnNull();

        $this->shipmentTransactionService->create(1, $this->transactionData);
    }
}
