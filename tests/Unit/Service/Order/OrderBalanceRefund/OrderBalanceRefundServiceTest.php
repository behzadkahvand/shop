<?php

namespace App\Tests\Unit\Service\Order\OrderBalanceRefund;

use App\Dictionary\TransactionStatus;
use App\DTO\Admin\OrderBalanceRefundData;
use App\Entity\Order;
use App\Entity\RefundDocument;
use App\Entity\Transaction;
use App\Events\Order\OrderBalanceAmountEvent;
use App\Repository\OrderRepository;
use App\Service\Notification\DTOs\AbstractNotificationDTO;
use App\Service\Notification\NotificationService;
use App\Service\Order\OrderBalanceRefund\Exceptions\InvalidOrderBalanceStatusException;
use App\Service\Order\OrderBalanceRefund\Exceptions\OrderNotFoundException;
use App\Service\Order\OrderBalanceRefund\OrderBalanceRefundFactory;
use App\Service\Order\OrderBalanceRefund\OrderBalanceRefundService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class OrderBalanceRefundServiceTest extends MockeryTestCase
{
    /**
     * @var OrderRepository|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $orderRepoMock;

    /**
     * @var EntityManagerInterface|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $em;

    /**
     * @var OrderBalanceRefundFactory|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $factoryMock;

    /**
     * @var NotificationService|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    private $notificationServiceMock;

    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|EventDispatcherInterface
     */
    protected $dispatcherMock;

    /**
     * @var Order|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $orderMock;

    /**
     * @var RefundDocument|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $refundDocumentMock;

    /**
     * @var Transaction|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $transactionMock;

    protected OrderBalanceRefundData $refundData;

    protected OrderBalanceRefundService $orderBalanceRefundService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderRepoMock           = Mockery::mock(OrderRepository::class);
        $this->em                      = Mockery::mock(EntityManagerInterface::class);
        $this->factoryMock             = Mockery::mock(OrderBalanceRefundFactory::class);
        $this->notificationServiceMock = Mockery::mock(NotificationService::class);
        $this->dispatcherMock          = Mockery::mock(EventDispatcherInterface::class);
        $this->orderMock               = Mockery::mock(Order::class);
        $this->refundDocumentMock      = Mockery::mock(RefundDocument::class);
        $this->transactionMock         = Mockery::mock(Transaction::class);
        $this->refundData              = new OrderBalanceRefundData();

        $this->refundData->setTrackingNumber('tracking-number-code')
                         ->setPaidAt(new DateTimeImmutable('now'));

        $this->orderBalanceRefundService = new OrderBalanceRefundService(
            true,
            $this->orderRepoMock,
            $this->em,
            $this->factoryMock,
            $this->notificationServiceMock,
            $this->dispatcherMock
        );
    }

    protected function tearDown(): void
    {
        unset($this->orderBalanceRefundService, $this->refundData);

        $this->orderRepoMock           = null;
        $this->em                      = null;
        $this->factoryMock             = null;
        $this->notificationServiceMock = null;
        $this->dispatcherMock          = null;
        $this->orderMock               = null;
        $this->refundDocumentMock      = null;
        $this->transactionMock         = null;
    }

    public function testItThrowsExceptionWhenOrderIsNotFound(): void
    {
        $this->orderRepoMock->shouldReceive('find')
                            ->once()
                            ->with(1)
                            ->andReturnNull();

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

        $this->expectException(OrderNotFoundException::class);
        $this->expectExceptionCode(404);
        $this->expectExceptionMessage('Order is not found for order balance refund action!');

        $this->orderBalanceRefundService->add(1, $this->refundData);
    }

    public function testItThrowsExceptionWhenOrderBalanceAmountEqualsToZero(): void
    {
        $this->orderRepoMock->shouldReceive('find')
                            ->once()
                            ->with(1)
                            ->andReturn($this->orderMock);

        $this->orderMock->shouldReceive('getBalanceAmount')
                    ->once()
                    ->withNoArgs()
                    ->andReturn(0);

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

        $this->expectException(InvalidOrderBalanceStatusException::class);
        $this->expectExceptionCode(422);
        $this->expectExceptionMessage('Order balance status is invalid for order balance refund action!');

        $this->orderBalanceRefundService->add(1, $this->refundData);
    }

    public function testItThrowsExceptionWhenOrderBalanceAmountIsNegative(): void
    {
        $this->orderRepoMock->shouldReceive('find')
                            ->once()
                            ->with(1)
                            ->andReturn($this->orderMock);

        $this->orderMock->shouldReceive('getBalanceAmount')
                    ->once()
                    ->withNoArgs()
                    ->andReturn(-100000);

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

        $this->expectException(InvalidOrderBalanceStatusException::class);
        $this->expectExceptionCode(422);
        $this->expectExceptionMessage('Order balance status is invalid for order balance refund action!');

        $this->orderBalanceRefundService->add(1, $this->refundData);
    }

    public function testItCanAddBalanceRefundWithDescription(): void
    {
        $this->refundData->setDescription('description');

        $this->orderRepoMock->shouldReceive('find')
                            ->once()
                            ->with(1)
                            ->andReturn($this->orderMock);

        $this->orderMock->shouldReceive('getBalanceAmount')
                        ->once()
                        ->withNoArgs()
                        ->andReturn(300000);

        $this->orderMock->shouldReceive('setBalanceAmount')
                        ->once()
                        ->with(0)
                        ->andReturn($this->orderMock);

        $this->em->shouldReceive('beginTransaction')
                 ->once()
                 ->withNoArgs()
                 ->andReturnNull();

        $this->factoryMock->shouldReceive('getRefundDocument')
                          ->once()
                          ->withNoArgs()
                          ->andReturn($this->refundDocumentMock);
        $this->factoryMock->shouldReceive('getTransaction')
                          ->once()
                          ->withNoArgs()
                          ->andReturn($this->transactionMock);

        $this->refundDocumentMock->shouldReceive('setOrder')
                                 ->once()
                                 ->with($this->orderMock)
                                 ->andReturn($this->refundDocumentMock);
        $this->refundDocumentMock->shouldReceive('setAmount')
                                 ->once()
                                 ->with(300000)
                                 ->andReturn($this->refundDocumentMock);
        $this->refundDocumentMock->shouldReceive('setDescription')
                                 ->once()
                                 ->with($this->refundData->getDescription())
                                 ->andReturn($this->refundDocumentMock);

        $this->em->shouldReceive('persist')
                 ->once()
                 ->with($this->refundDocumentMock)
                 ->andReturnNull();

        $this->transactionMock->shouldReceive('setAmount')
                              ->once()
                              ->with(300000)
                              ->andReturn($this->transactionMock);
        $this->transactionMock->shouldReceive('setDocument')
                              ->once()
                              ->with($this->refundDocumentMock)
                              ->andReturn($this->transactionMock);
        $this->transactionMock->shouldReceive('setGateway')
                              ->once()
                              ->with(OrderBalanceRefundService::GATEWAY_NAME)
                              ->andReturn($this->transactionMock);
        $this->transactionMock->shouldReceive('setPaidAt')
                              ->once()
                              ->with($this->refundData->getPaidAt())
                              ->andReturn($this->transactionMock);
        $this->transactionMock->shouldReceive('setTrackingNumber')
                              ->once()
                              ->with($this->refundData->getTrackingNumber())
                              ->andReturn($this->transactionMock);
        $this->transactionMock->shouldReceive('setStatus')
                              ->once()
                              ->with(TransactionStatus::SUCCESS)
                              ->andReturn($this->transactionMock);

        $this->orderMock->shouldReceive('getId')
                        ->once()
                        ->withNoArgs()
                        ->andReturn(59);

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

        $this->dispatcherMock->shouldReceive('dispatch')
                             ->once()
                             ->with(Mockery::type(OrderBalanceAmountEvent::class));

        $this->notificationServiceMock->shouldReceive('send')
                                      ->once()
                                      ->with(Mockery::type(AbstractNotificationDTO::class))
                                      ->andReturn();

        $this->orderBalanceRefundService->add(1, $this->refundData);
    }

    public function testItCanAddBalanceRefundWithoutDescription(): void
    {
        $this->orderRepoMock->shouldReceive('find')
                            ->once()
                            ->with(1)
                            ->andReturn($this->orderMock);

        $this->orderMock->shouldReceive('getBalanceAmount')
                        ->once()
                        ->withNoArgs()
                        ->andReturn(300000);

        $this->orderMock->shouldReceive('setBalanceAmount')
                        ->once()
                        ->with(0)
                        ->andReturn($this->orderMock);

        $this->em->shouldReceive('beginTransaction')
                 ->once()
                 ->withNoArgs()
                 ->andReturnNull();

        $this->factoryMock->shouldReceive('getRefundDocument')
                          ->once()
                          ->withNoArgs()
                          ->andReturn($this->refundDocumentMock);
        $this->factoryMock->shouldReceive('getTransaction')
                          ->once()
                          ->withNoArgs()
                          ->andReturn($this->transactionMock);

        $this->refundDocumentMock->shouldReceive('setOrder')
                                 ->once()
                                 ->with($this->orderMock)
                                 ->andReturn($this->refundDocumentMock);
        $this->refundDocumentMock->shouldReceive('setAmount')
                                 ->once()
                                 ->with(300000)
                                 ->andReturn($this->refundDocumentMock);

        $this->em->shouldReceive('persist')
                 ->once()
                 ->with($this->refundDocumentMock)
                 ->andReturnNull();

        $this->transactionMock->shouldReceive('setAmount')
                              ->once()
                              ->with(300000)
                              ->andReturn($this->transactionMock);
        $this->transactionMock->shouldReceive('setDocument')
                              ->once()
                              ->with($this->refundDocumentMock)
                              ->andReturn($this->transactionMock);
        $this->transactionMock->shouldReceive('setGateway')
                              ->once()
                              ->with(OrderBalanceRefundService::GATEWAY_NAME)
                              ->andReturn($this->transactionMock);
        $this->transactionMock->shouldReceive('setPaidAt')
                              ->once()
                              ->with($this->refundData->getPaidAt())
                              ->andReturn($this->transactionMock);
        $this->transactionMock->shouldReceive('setTrackingNumber')
                              ->once()
                              ->with($this->refundData->getTrackingNumber())
                              ->andReturn($this->transactionMock);
        $this->transactionMock->shouldReceive('setStatus')
                              ->once()
                              ->with(TransactionStatus::SUCCESS)
                              ->andReturn($this->transactionMock);

        $this->orderMock->shouldReceive('getId')
                        ->once()
                        ->withNoArgs()
                        ->andReturn(59);

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

        $this->dispatcherMock->shouldReceive('dispatch')
                             ->once()
                             ->with(Mockery::type(OrderBalanceAmountEvent::class));

        $this->notificationServiceMock->shouldReceive('send')
                                      ->once()
                                      ->with(Mockery::type(AbstractNotificationDTO::class))
                                      ->andReturn();

        $this->orderBalanceRefundService->add(1, $this->refundData);
    }

    public function testItCanAddBalanceRefundWithDescriptionAndWithoutSendingNotification(): void
    {
        $this->refundData->setDescription('description');

        $this->orderRepoMock->shouldReceive('find')
                            ->once()
                            ->with(1)
                            ->andReturn($this->orderMock);

        $this->orderMock->shouldReceive('getBalanceAmount')
                        ->once()
                        ->withNoArgs()
                        ->andReturn(300000);

        $this->orderMock->shouldReceive('setBalanceAmount')
                        ->once()
                        ->with(0)
                        ->andReturn($this->orderMock);

        $this->em->shouldReceive('beginTransaction')
                 ->once()
                 ->withNoArgs()
                 ->andReturnNull();

        $this->factoryMock->shouldReceive('getRefundDocument')
                          ->once()
                          ->withNoArgs()
                          ->andReturn($this->refundDocumentMock);
        $this->factoryMock->shouldReceive('getTransaction')
                          ->once()
                          ->withNoArgs()
                          ->andReturn($this->transactionMock);

        $this->refundDocumentMock->shouldReceive('setOrder')
                                 ->once()
                                 ->with($this->orderMock)
                                 ->andReturn($this->refundDocumentMock);
        $this->refundDocumentMock->shouldReceive('setAmount')
                                 ->once()
                                 ->with(300000)
                                 ->andReturn($this->refundDocumentMock);
        $this->refundDocumentMock->shouldReceive('setDescription')
                                 ->once()
                                 ->with($this->refundData->getDescription())
                                 ->andReturn($this->refundDocumentMock);

        $this->em->shouldReceive('persist')
                 ->once()
                 ->with($this->refundDocumentMock)
                 ->andReturnNull();

        $this->transactionMock->shouldReceive('setAmount')
                              ->once()
                              ->with(300000)
                              ->andReturn($this->transactionMock);
        $this->transactionMock->shouldReceive('setDocument')
                              ->once()
                              ->with($this->refundDocumentMock)
                              ->andReturn($this->transactionMock);
        $this->transactionMock->shouldReceive('setGateway')
                              ->once()
                              ->with(OrderBalanceRefundService::GATEWAY_NAME)
                              ->andReturn($this->transactionMock);
        $this->transactionMock->shouldReceive('setPaidAt')
                              ->once()
                              ->with($this->refundData->getPaidAt())
                              ->andReturn($this->transactionMock);
        $this->transactionMock->shouldReceive('setTrackingNumber')
                              ->once()
                              ->with($this->refundData->getTrackingNumber())
                              ->andReturn($this->transactionMock);
        $this->transactionMock->shouldReceive('setStatus')
                              ->once()
                              ->with(TransactionStatus::SUCCESS)
                              ->andReturn($this->transactionMock);

        $this->orderMock->shouldReceive('getId')
                        ->once()
                        ->withNoArgs()
                        ->andReturn(59);

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

        $this->dispatcherMock->shouldReceive('dispatch')
                             ->once()
                             ->with(Mockery::type(OrderBalanceAmountEvent::class));

        $this->orderBalanceRefundService = new OrderBalanceRefundService(
            false,
            $this->orderRepoMock,
            $this->em,
            $this->factoryMock,
            $this->notificationServiceMock,
            $this->dispatcherMock
        );

        $this->orderBalanceRefundService->add(1, $this->refundData);
    }

    public function testItCanAddBalanceRefundWithoutDescriptionAndWithoutSendingNotification(): void
    {
        $this->orderRepoMock->shouldReceive('find')
                            ->once()
                            ->with(1)
                            ->andReturn($this->orderMock);

        $this->orderMock->shouldReceive('getBalanceAmount')
                        ->once()
                        ->withNoArgs()
                        ->andReturn(300000);

        $this->orderMock->shouldReceive('setBalanceAmount')
                        ->once()
                        ->with(0)
                        ->andReturn($this->orderMock);

        $this->em->shouldReceive('beginTransaction')
                 ->once()
                 ->withNoArgs()
                 ->andReturnNull();

        $this->factoryMock->shouldReceive('getRefundDocument')
                          ->once()
                          ->withNoArgs()
                          ->andReturn($this->refundDocumentMock);
        $this->factoryMock->shouldReceive('getTransaction')
                          ->once()
                          ->withNoArgs()
                          ->andReturn($this->transactionMock);

        $this->refundDocumentMock->shouldReceive('setOrder')
                                 ->once()
                                 ->with($this->orderMock)
                                 ->andReturn($this->refundDocumentMock);
        $this->refundDocumentMock->shouldReceive('setAmount')
                                 ->once()
                                 ->with(300000)
                                 ->andReturn($this->refundDocumentMock);

        $this->em->shouldReceive('persist')
                 ->once()
                 ->with($this->refundDocumentMock)
                 ->andReturnNull();

        $this->transactionMock->shouldReceive('setAmount')
                              ->once()
                              ->with(300000)
                              ->andReturn($this->transactionMock);
        $this->transactionMock->shouldReceive('setDocument')
                              ->once()
                              ->with($this->refundDocumentMock)
                              ->andReturn($this->transactionMock);
        $this->transactionMock->shouldReceive('setGateway')
                              ->once()
                              ->with(OrderBalanceRefundService::GATEWAY_NAME)
                              ->andReturn($this->transactionMock);
        $this->transactionMock->shouldReceive('setPaidAt')
                              ->once()
                              ->with($this->refundData->getPaidAt())
                              ->andReturn($this->transactionMock);
        $this->transactionMock->shouldReceive('setTrackingNumber')
                              ->once()
                              ->with($this->refundData->getTrackingNumber())
                              ->andReturn($this->transactionMock);
        $this->transactionMock->shouldReceive('setStatus')
                              ->once()
                              ->with(TransactionStatus::SUCCESS)
                              ->andReturn($this->transactionMock);

        $this->orderMock->shouldReceive('getId')
                        ->once()
                        ->withNoArgs()
                        ->andReturn(59);

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

        $this->dispatcherMock->shouldReceive('dispatch')
                             ->once()
                             ->with(Mockery::type(OrderBalanceAmountEvent::class));

        $this->orderBalanceRefundService = new OrderBalanceRefundService(
            false,
            $this->orderRepoMock,
            $this->em,
            $this->factoryMock,
            $this->notificationServiceMock,
            $this->dispatcherMock
        );

        $this->orderBalanceRefundService->add(1, $this->refundData);
    }

    public function testItCanAddBalanceRefundWithRefundAmountAndForceFlag(): void
    {
        $this->refundData->setForce(true)
            ->setAmount(100000);

        $this->orderRepoMock->shouldReceive('find')
                            ->once()
                            ->with(1)
                            ->andReturn($this->orderMock);

        $this->orderMock->shouldReceive('getBalanceAmount')
                        ->once()
                        ->withNoArgs()
                        ->andReturn(300000);

        $this->orderMock->shouldReceive('setBalanceAmount')
                        ->once()
                        ->with(0)
                        ->andReturn($this->orderMock);

        $this->em->shouldReceive('beginTransaction')
                 ->once()
                 ->withNoArgs()
                 ->andReturnNull();

        $this->factoryMock->shouldReceive('getRefundDocument')
                          ->once()
                          ->withNoArgs()
                          ->andReturn($this->refundDocumentMock);
        $this->factoryMock->shouldReceive('getTransaction')
                          ->once()
                          ->withNoArgs()
                          ->andReturn($this->transactionMock);

        $this->refundDocumentMock->shouldReceive('setOrder')
                                 ->once()
                                 ->with($this->orderMock)
                                 ->andReturn($this->refundDocumentMock);
        $this->refundDocumentMock->shouldReceive('setAmount')
                                 ->once()
                                 ->with(100000)
                                 ->andReturn($this->refundDocumentMock);

        $this->em->shouldReceive('persist')
                 ->once()
                 ->with($this->refundDocumentMock)
                 ->andReturnNull();

        $this->transactionMock->shouldReceive('setAmount')
                              ->once()
                              ->with(100000)
                              ->andReturn($this->transactionMock);
        $this->transactionMock->shouldReceive('setDocument')
                              ->once()
                              ->with($this->refundDocumentMock)
                              ->andReturn($this->transactionMock);
        $this->transactionMock->shouldReceive('setGateway')
                              ->once()
                              ->with(OrderBalanceRefundService::GATEWAY_NAME)
                              ->andReturn($this->transactionMock);
        $this->transactionMock->shouldReceive('setPaidAt')
                              ->once()
                              ->with($this->refundData->getPaidAt())
                              ->andReturn($this->transactionMock);
        $this->transactionMock->shouldReceive('setTrackingNumber')
                              ->once()
                              ->with($this->refundData->getTrackingNumber())
                              ->andReturn($this->transactionMock);
        $this->transactionMock->shouldReceive('setStatus')
                              ->once()
                              ->with(TransactionStatus::SUCCESS)
                              ->andReturn($this->transactionMock);

        $this->orderMock->shouldReceive('getId')
                        ->once()
                        ->withNoArgs()
                        ->andReturn(59);

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

        $this->dispatcherMock->shouldReceive('dispatch')
                             ->once()
                             ->with(Mockery::type(OrderBalanceAmountEvent::class));

        $this->notificationServiceMock->shouldReceive('send')
                                      ->once()
                                      ->with(Mockery::type(AbstractNotificationDTO::class))
                                      ->andReturn();

        $this->orderBalanceRefundService->add(1, $this->refundData);
    }

    public function testItCanAddBalanceRefundWithRefundAmount(): void
    {
        $this->refundData->setAmount(100000);

        $this->orderRepoMock->shouldReceive('find')
                            ->once()
                            ->with(1)
                            ->andReturn($this->orderMock);

        $this->orderMock->shouldReceive('getBalanceAmount')
                        ->once()
                        ->withNoArgs()
                        ->andReturn(300000);

        $this->orderMock->shouldReceive('setBalanceAmount')
                        ->once()
                        ->with(0)
                        ->andReturn($this->orderMock);

        $this->em->shouldReceive('beginTransaction')
                 ->once()
                 ->withNoArgs()
                 ->andReturnNull();

        $this->factoryMock->shouldReceive('getRefundDocument')
                          ->once()
                          ->withNoArgs()
                          ->andReturn($this->refundDocumentMock);
        $this->factoryMock->shouldReceive('getTransaction')
                          ->once()
                          ->withNoArgs()
                          ->andReturn($this->transactionMock);

        $this->refundDocumentMock->shouldReceive('setOrder')
                                 ->once()
                                 ->with($this->orderMock)
                                 ->andReturn($this->refundDocumentMock);
        $this->refundDocumentMock->shouldReceive('setAmount')
                                 ->once()
                                 ->with(300000)
                                 ->andReturn($this->refundDocumentMock);

        $this->em->shouldReceive('persist')
                 ->once()
                 ->with($this->refundDocumentMock)
                 ->andReturnNull();

        $this->transactionMock->shouldReceive('setAmount')
                              ->once()
                              ->with(300000)
                              ->andReturn($this->transactionMock);
        $this->transactionMock->shouldReceive('setDocument')
                              ->once()
                              ->with($this->refundDocumentMock)
                              ->andReturn($this->transactionMock);
        $this->transactionMock->shouldReceive('setGateway')
                              ->once()
                              ->with(OrderBalanceRefundService::GATEWAY_NAME)
                              ->andReturn($this->transactionMock);
        $this->transactionMock->shouldReceive('setPaidAt')
                              ->once()
                              ->with($this->refundData->getPaidAt())
                              ->andReturn($this->transactionMock);
        $this->transactionMock->shouldReceive('setTrackingNumber')
                              ->once()
                              ->with($this->refundData->getTrackingNumber())
                              ->andReturn($this->transactionMock);
        $this->transactionMock->shouldReceive('setStatus')
                              ->once()
                              ->with(TransactionStatus::SUCCESS)
                              ->andReturn($this->transactionMock);

        $this->orderMock->shouldReceive('getId')
                        ->once()
                        ->withNoArgs()
                        ->andReturn(59);

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

        $this->dispatcherMock->shouldReceive('dispatch')
                             ->once()
                             ->with(Mockery::type(OrderBalanceAmountEvent::class));

        $this->notificationServiceMock->shouldReceive('send')
                                      ->once()
                                      ->with(Mockery::type(AbstractNotificationDTO::class))
                                      ->andReturn();

        $this->orderBalanceRefundService->add(1, $this->refundData);
    }

    public function testItCanAddBalanceRefundWithForceFlag(): void
    {
        $this->refundData->setForce(true);

        $this->orderRepoMock->shouldReceive('find')
                            ->once()
                            ->with(1)
                            ->andReturn($this->orderMock);

        $this->orderMock->shouldReceive('getBalanceAmount')
                        ->once()
                        ->withNoArgs()
                        ->andReturn(300000);

        $this->orderMock->shouldReceive('setBalanceAmount')
                        ->once()
                        ->with(0)
                        ->andReturn($this->orderMock);

        $this->em->shouldReceive('beginTransaction')
                 ->once()
                 ->withNoArgs()
                 ->andReturnNull();

        $this->factoryMock->shouldReceive('getRefundDocument')
                          ->once()
                          ->withNoArgs()
                          ->andReturn($this->refundDocumentMock);
        $this->factoryMock->shouldReceive('getTransaction')
                          ->once()
                          ->withNoArgs()
                          ->andReturn($this->transactionMock);

        $this->refundDocumentMock->shouldReceive('setOrder')
                                 ->once()
                                 ->with($this->orderMock)
                                 ->andReturn($this->refundDocumentMock);
        $this->refundDocumentMock->shouldReceive('setAmount')
                                 ->once()
                                 ->with(300000)
                                 ->andReturn($this->refundDocumentMock);

        $this->em->shouldReceive('persist')
                 ->once()
                 ->with($this->refundDocumentMock)
                 ->andReturnNull();

        $this->transactionMock->shouldReceive('setAmount')
                              ->once()
                              ->with(300000)
                              ->andReturn($this->transactionMock);
        $this->transactionMock->shouldReceive('setDocument')
                              ->once()
                              ->with($this->refundDocumentMock)
                              ->andReturn($this->transactionMock);
        $this->transactionMock->shouldReceive('setGateway')
                              ->once()
                              ->with(OrderBalanceRefundService::GATEWAY_NAME)
                              ->andReturn($this->transactionMock);
        $this->transactionMock->shouldReceive('setPaidAt')
                              ->once()
                              ->with($this->refundData->getPaidAt())
                              ->andReturn($this->transactionMock);
        $this->transactionMock->shouldReceive('setTrackingNumber')
                              ->once()
                              ->with($this->refundData->getTrackingNumber())
                              ->andReturn($this->transactionMock);
        $this->transactionMock->shouldReceive('setStatus')
                              ->once()
                              ->with(TransactionStatus::SUCCESS)
                              ->andReturn($this->transactionMock);

        $this->orderMock->shouldReceive('getId')
                        ->once()
                        ->withNoArgs()
                        ->andReturn(59);

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

        $this->dispatcherMock->shouldReceive('dispatch')
                             ->once()
                             ->with(Mockery::type(OrderBalanceAmountEvent::class));

        $this->notificationServiceMock->shouldReceive('send')
                                      ->once()
                                      ->with(Mockery::type(AbstractNotificationDTO::class))
                                      ->andReturn();

        $this->orderBalanceRefundService->add(1, $this->refundData);
    }
}
