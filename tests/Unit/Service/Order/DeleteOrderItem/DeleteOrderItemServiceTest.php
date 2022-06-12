<?php

namespace App\Tests\Unit\Service\Order\DeleteOrderItem;

use App\Dictionary\OrderStatus;
use App\Dictionary\TransferReason;
use App\Entity\Admin;
use App\Entity\Order;
use App\Entity\OrderDocument;
use App\Entity\OrderItem;
use App\Entity\OrderShipment;
use App\Entity\SellerOrderItem;
use App\Events\Order\OrderBalanceAmountEvent;
use App\Exceptions\UnremovableEntityException;
use App\Repository\OrderItemRepository;
use App\Service\Order\DeleteOrderItem\DeleteOrderItemService;
use App\Service\Order\DeleteOrderItem\Event\OrderItemRemoved;
use App\Service\Order\DeleteOrderItem\Exceptions\OrderItemNotFoundException;
use App\Service\Order\RecalculateOrderDocument\RecalculateOrderDocument;
use App\Service\Order\UpdateOrderItems\Exceptions\OrderDocumentNotFoundException;
use App\Service\Order\Wallet\OrderWalletPaymentHandler;
use App\Service\Seller\SellerOrderItem\Status\SellerOrderItemStatusService;
use App\Tests\Unit\BaseUnitTestCase;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Throwable;

class DeleteOrderItemServiceTest extends BaseUnitTestCase
{
    private ?DeleteOrderItemService $deleteOrderItemService;
    private LegacyMockInterface|EntityManagerInterface|MockInterface|null $manager;
    private OrderItemRepository|LegacyMockInterface|MockInterface|null $orderItemRepository;
    private LegacyMockInterface|MockInterface|SellerOrderItemStatusService|null $sellerOrderItemStatusService;
    private SellerOrderItem|LegacyMockInterface|MockInterface|null $sellerOrderItem;
    private LegacyMockInterface|OrderItem|MockInterface|null $orderItem;
    private Order|LegacyMockInterface|MockInterface|null $order;
    private Admin|LegacyMockInterface|MockInterface|null $admin;
    private OrderDocument|LegacyMockInterface|MockInterface|null $orderDocument;
    private LegacyMockInterface|EventDispatcherInterface|MockInterface|null $eventDispatcher;
    private LegacyMockInterface|OrderShipment|MockInterface|null $shipment;
    private LegacyMockInterface|MockInterface|RecalculateOrderDocument|null $recalculateDocumentMock;
    private LegacyMockInterface|MockInterface|OrderWalletPaymentHandler|null $walletPaymentHandler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->manager                      = Mockery::mock(EntityManagerInterface::class);
        $this->orderItemRepository          = Mockery::mock(OrderItemRepository::class);
        $this->orderItem                    = Mockery::mock(OrderItem::class);
        $this->sellerOrderItem              = Mockery::mock(SellerOrderItem::class);
        $this->sellerOrderItemStatusService = Mockery::mock(SellerOrderItemStatusService::class);
        $this->order                        = Mockery::mock(Order::class);
        $this->orderDocument                = Mockery::mock(OrderDocument::class);
        $this->eventDispatcher              = Mockery::mock(EventDispatcherInterface::class);
        $this->admin                        = Mockery::mock(Admin::class);
        $this->shipment                     = Mockery::mock(OrderShipment::class);
        $this->recalculateDocumentMock      = Mockery::mock(RecalculateOrderDocument::class);
        $this->walletPaymentHandler = Mockery::mock(OrderWalletPaymentHandler::class);

        $this->deleteOrderItemService = new DeleteOrderItemService(
            $this->manager,
            $this->orderItemRepository,
            $this->sellerOrderItemStatusService,
            $this->eventDispatcher,
            $this->recalculateDocumentMock,
            $this->walletPaymentHandler
        );
    }

    public function testItCanDeleteAnOrderItem(): void
    {
        $this->manager->shouldReceive('beginTransaction')
                      ->once()
                      ->withNoArgs()
                      ->andReturn();

        $this->orderItemRepository->shouldReceive('findOrFail')
                                  ->once()
                                  ->with(1)
                                  ->andReturn($this->orderItem);

        $this->manager->shouldReceive('lock')
                      ->once()
                      ->with($this->orderItem, LockMode::PESSIMISTIC_READ)
                      ->andReturn();

        $this->orderItem->shouldReceive('getOrder')
                        ->once()
                        ->withNoArgs()
                        ->andReturn($this->order);

        $this->orderItem->shouldReceive('getOrderShipment')
                        ->twice()
                        ->withNoArgs()
                        ->andReturn($this->shipment);

        $this->shipment->shouldReceive('removeOrderItem')
                       ->once()
                       ->with($this->orderItem)
                       ->andReturnSelf();

        $this->shipment->shouldReceive('hasOnlyOneDistinctItem')
                       ->once()
                       ->withNoArgs()
                       ->andReturnFalse();

        $this->manager->shouldReceive('lock')
                      ->once()
                      ->with($this->order, LockMode::PESSIMISTIC_READ)
                      ->andReturn();

        $this->orderItem->shouldReceive(['getSubTotal' => 1000, 'getGrandTotal' => 1000]);
        $this->orderItem->shouldReceive('releaseReservedStock')->once()->withNoArgs()->andReturn();

        $this->order->shouldReceive(['getSubTotal' => 5000, 'getGrandTotal' => 5000]);
        $this->order->shouldReceive('removeOrderItem')
                    ->once()
                    ->with($this->orderItem)
                    ->andReturnSelf();
        $this->order->shouldReceive('getStatus')
                    ->once()
                    ->withNoArgs()
                    ->andReturn(OrderStatus::WAIT_CUSTOMER);

        $this->orderItem->shouldReceive('getSellerOrderItem')
                        ->once()
                        ->withNoArgs()
                        ->andReturn($this->sellerOrderItem);

        $this->order->shouldReceive('getOrderItems->count')
                    ->once()
                    ->withNoArgs()
                    ->andReturn(5);

        $this->order->shouldReceive('getId')
                    ->once()
                    ->withNoArgs()
                    ->andReturn(59);

        $this->sellerOrderItemStatusService->shouldReceive('change')
                                           ->once()
                                           ->andReturn();

        $this->recalculateDocumentMock->shouldReceive('perform')
                                      ->once()
                                      ->with($this->order)
                                      ->andReturn();

        $this->manager->shouldReceive('remove')
                      ->once()
                      ->with($this->orderItem)
                      ->andReturn();

        $this->manager->shouldReceive('flush')
                      ->once()
                      ->andReturn();

        $this->manager->shouldReceive('commit')
                      ->once()
                      ->andReturn();

        $this->eventDispatcher->shouldReceive('dispatch')
                              ->once()
                              ->with(Mockery::type(OrderItemRemoved::class));

        $this->eventDispatcher->shouldReceive('dispatch')
                              ->once()
                              ->with(Mockery::type(OrderBalanceAmountEvent::class));

        $this->walletPaymentHandler
            ->shouldReceive('handle')
            ->once()
            ->with($this->order, TransferReason::ORDER_REFUND)
            ->andReturnNull();

        $this->deleteOrderItemService->perform(1, $this->admin);
    }

    /**
     * @throws OrderDocumentNotFoundException
     * @throws Throwable
     * @dataProvider statusProvider
     */
    public function testShouldNotCallReleaseStockOnOrderItemIfOrderHasOneDistinctItemAndOrderStatusIsNotWaitCustomer(string $status): void
    {
        $this->manager->shouldReceive('beginTransaction')
                      ->once()
                      ->withNoArgs()
                      ->andReturn();

        $this->orderItemRepository->shouldReceive('findOrFail')
                                  ->once()
                                  ->with(1)
                                  ->andReturn($this->orderItem);

        $this->manager->shouldReceive('lock')
                      ->once()
                      ->with($this->orderItem, LockMode::PESSIMISTIC_READ)
                      ->andReturn();

        $this->orderItem->shouldReceive('getOrder')
                        ->once()
                        ->withNoArgs()
                        ->andReturn($this->order);

        $this->orderItem->shouldReceive('getOrderShipment')
                        ->twice()
                        ->withNoArgs()
                        ->andReturn($this->shipment);

        $this->shipment->shouldReceive('removeOrderItem')
                       ->once()
                       ->with($this->orderItem)
                       ->andReturnSelf();

        $this->shipment->shouldReceive('hasOnlyOneDistinctItem')
                       ->once()
                       ->withNoArgs()
                       ->andReturnFalse();

        $this->manager->shouldReceive('lock')
                      ->once()
                      ->with($this->order, LockMode::PESSIMISTIC_READ)
                      ->andReturn();

        $this->orderItem->shouldReceive(['getSubTotal' => 1000, 'getGrandTotal' => 1000]);

        $this->order->shouldReceive(['getSubTotal' => 5000, 'getGrandTotal' => 5000]);
        $this->order->shouldReceive('removeOrderItem')
                    ->once()
                    ->with($this->orderItem)
                    ->andReturnSelf();
        $this->order->shouldReceive('getStatus')
                    ->once()
                    ->withNoArgs()
                    ->andReturn($status);

        $this->orderItem->shouldReceive('getSellerOrderItem')
                        ->once()
                        ->withNoArgs()
                        ->andReturn($this->sellerOrderItem);

        $this->order->shouldReceive('getOrderItems->count')
                    ->once()
                    ->withNoArgs()
                    ->andReturn(5);

        $this->order->shouldReceive('getId')
                    ->once()
                    ->withNoArgs()
                    ->andReturn(59);

        $this->sellerOrderItemStatusService->shouldReceive('change')
                                           ->once()
                                           ->andReturn();

        $this->recalculateDocumentMock->shouldReceive('perform')
                                      ->once()
                                      ->with($this->order)
                                      ->andReturn();

        $this->manager->shouldReceive('remove')
                      ->once()
                      ->with($this->orderItem)
                      ->andReturn();

        $this->manager->shouldReceive('flush')
                      ->once()
                      ->andReturn();

        $this->manager->shouldReceive('commit')
                      ->once()
                      ->andReturn();

        $this->eventDispatcher->shouldReceive('dispatch')
                              ->once()
                              ->with(Mockery::type(OrderItemRemoved::class));

        $this->eventDispatcher->shouldReceive('dispatch')
                              ->once()
                              ->with(Mockery::type(OrderBalanceAmountEvent::class));

        $this->walletPaymentHandler
            ->shouldReceive('handle')
            ->once()
            ->with($this->order, TransferReason::ORDER_REFUND)
            ->andReturnNull();

        $this->deleteOrderItemService->perform(1, $this->admin);
    }

    public function statusProvider(): array
    {
        return [
            [OrderStatus::NEW],
            [OrderStatus::CALL_FAILED],
            [OrderStatus::WAITING_FOR_PAY],
            [OrderStatus::CONFIRMED],
            [OrderStatus::DELIVERED],
            [OrderStatus::CANCELED],
            [OrderStatus::CANCELED_SYSTEM],
            [OrderStatus::REFUND],
        ];
    }

    public function testItFailsIfAnExceptionHasThrown(): void
    {
        $this->expectException(OrderItemNotFoundException::class);

        $this->manager->shouldReceive('beginTransaction')
                      ->once()
                      ->withNoArgs()
                      ->andReturn();

        $this->orderItemRepository->shouldReceive('findOrFail')
                                  ->once()
                                  ->with(1)
                                  ->andThrowExceptions([new OrderItemNotFoundException()]);

        $this->manager->shouldReceive('close')
                      ->once()
                      ->withNoArgs()
                      ->andReturn();
        $this->manager->shouldReceive('rollback')
                      ->once()
                      ->withNoArgs()
                      ->andReturn();

        $this->deleteOrderItemService->perform(1, $this->admin);
    }

    public function testShouldThrowExceptionIfItemIsTheOnlyItemInItsShipment(): void
    {
        $this->manager->shouldReceive('beginTransaction')
                      ->once()
                      ->withNoArgs()
                      ->andReturn();

        $this->orderItemRepository->shouldReceive('findOrFail')
                                  ->once()
                                  ->with(1)
                                  ->andReturn($this->orderItem);

        $this->orderItem->shouldReceive('getOrderShipment')
                        ->once()
                        ->withNoArgs()
                        ->andReturn($this->shipment);

        $this->shipment->shouldReceive('hasOnlyOneDistinctItem')
                       ->once()
                       ->withNoArgs()
                       ->andReturnTrue();

        $this->manager->shouldReceive('close')
                      ->once()
                      ->withNoArgs()
                      ->andReturn();
        $this->manager->shouldReceive('rollback')
                      ->once()
                      ->withNoArgs()
                      ->andReturn();

        $this->expectException(UnremovableEntityException::class);

        $this->deleteOrderItemService->perform(1, $this->admin);
    }
}
