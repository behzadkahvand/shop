<?php

namespace App\Tests\Unit\Service\Order\UpdateOrderItems;

use App\Dictionary\OrderStatus;
use App\Dictionary\TransferReason;
use App\Entity\Admin;
use App\Entity\Inventory;
use App\Entity\Order;
use App\Entity\OrderDocument;
use App\Entity\OrderItem;
use App\Repository\OrderRepository;
use App\Service\Order\RecalculateOrderDocument\RecalculateOrderDocument;
use App\Service\Order\UpdateOrderItems\Event\OrderItemUpdated;
use App\Service\Order\UpdateOrderItems\Exceptions\InvalidOrderException;
use App\Service\Order\UpdateOrderItems\Exceptions\InvalidOrderItemIdException;
use App\Service\Order\UpdateOrderItems\Exceptions\OrderDocumentNotFoundException;
use App\Service\Order\UpdateOrderItems\UpdateOrderItemsService;
use App\Service\Order\Wallet\OrderWalletPaymentHandler;
use App\Service\OrderItemLog\OrderItemLogCreator;
use App\Tests\Unit\BaseUnitTestCase;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class UpdateOrderItemsServiceTest extends BaseUnitTestCase
{
    protected LegacyMockInterface|MockInterface|OrderRepository|null $orderRepoMock;

    protected LegacyMockInterface|MockInterface|EntityManager|null $em;

    protected Order|LegacyMockInterface|MockInterface|null $orderMock;

    protected LegacyMockInterface|OrderItem|MockInterface|null $orderItemMock;

    protected OrderDocument|LegacyMockInterface|MockInterface|null $orderDocumentMock;

    protected OrderItemLogCreator|LegacyMockInterface|MockInterface|null $logCreatorMock;

    protected ?array $items;

    protected LegacyMockInterface|Inventory|MockInterface|null $inventoryMock;

    protected EventDispatcher|LegacyMockInterface|MockInterface|null $dispatcherMock;

    protected LegacyMockInterface|MockInterface|RecalculateOrderDocument|null $recalculateDocumentMock;

    protected ?UpdateOrderItemsService $updateOrderItems;
    /**
     * @var \App\Service\Order\Wallet\OrderWalletPaymentHandler|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private LegacyMockInterface|MockInterface|OrderWalletPaymentHandler|null $walletPaymentHandler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderRepoMock           = Mockery::mock(OrderRepository::class);
        $this->em                      = Mockery::mock(EntityManager::class);
        $this->orderMock               = Mockery::mock(Order::class);
        $this->orderItemMock           = Mockery::mock(OrderItem::class);
        $this->orderDocumentMock       = Mockery::mock(OrderDocument::class);
        $this->logCreatorMock          = Mockery::mock(OrderItemLogCreator::class);
        $this->dispatcherMock          = Mockery::mock(EventDispatcher::class);
        $this->inventoryMock           = Mockery::mock(Inventory::class);
        $this->recalculateDocumentMock = Mockery::mock(RecalculateOrderDocument::class);
        $this->walletPaymentHandler = Mockery::mock(OrderWalletPaymentHandler::class);

        $this->updateOrderItems = new UpdateOrderItemsService(
            $this->orderRepoMock,
            $this->em,
            $this->logCreatorMock,
            $this->dispatcherMock,
            $this->recalculateDocumentMock,
            $this->walletPaymentHandler
        );

        $this->items = [
            [
                'id'    => 1,
                'price' => 55000,
            ],
            [
                'id'    => 2,
                'price' => 27000,
            ],
        ];
    }

    public function testItThrowsExceptionWhenOrderIsInvalid(): void
    {
        $validStatuses = [
            OrderStatus::WAIT_CUSTOMER,
            OrderStatus::WAITING_FOR_PAY,
            OrderStatus::CONFIRMED
        ];
        $this->orderRepoMock->shouldReceive('findOrdersWithStatusAndId')
                            ->once()
                            ->with(1, $validStatuses)
                            ->andReturnNull();

        $this->expectException(InvalidOrderException::class);
        $this->expectExceptionCode(422);
        $this->expectExceptionMessage('Order is invalid for updating order items action!');

        $this->updateOrderItems->perform(1, $this->items, new Admin());
    }

    public function testItThrowsExceptionWhenOrderItemIdIsInvalid(): void
    {
        $validStatuses = [
            OrderStatus::WAIT_CUSTOMER,
            OrderStatus::WAITING_FOR_PAY,
            OrderStatus::CONFIRMED
        ];
        $this->orderRepoMock->shouldReceive('findOrdersWithStatusAndId')
                            ->once()
                            ->with(1, $validStatuses)
                            ->andReturn($this->orderMock);

        $this->orderMock->shouldReceive('getOrderItems')
                        ->once()
                        ->withNoArgs()
                        ->andReturn(new ArrayCollection([
                            $this->orderItemMock,
                            $this->orderItemMock,
                        ]));

        $this->orderItemMock->shouldReceive('getId')
                            ->twice()
                            ->withNoArgs()
                            ->andReturn(1, 3);
        $this->orderItemMock->shouldReceive('getPrice')
                            ->once()
                            ->withNoArgs()
                            ->andReturn(50000);
        $this->orderItemMock->shouldReceive('getQuantity')
                            ->once()
                            ->withNoArgs()
                            ->andReturn(1);
        $this->orderItemMock->shouldReceive('getGrandTotal')
                            ->once()
                            ->withNoArgs()
                            ->andReturn(100);
        $this->orderItemMock->shouldReceive('setSubtotal')
                            ->once()
                            ->with(50000)
                            ->andReturn($this->orderItemMock);
        $this->orderItemMock->shouldReceive('setGrandTotal')
                            ->once()
                            ->with(55000)
                            ->andReturn($this->orderItemMock);
        $this->orderItemMock->shouldReceive('setQuantity')
                            ->once()
                            ->with(1)
                            ->andReturn($this->orderItemMock);

        $this->dispatcherMock->shouldReceive('dispatch')
                             ->once()
                             ->with(Mockery::type(OrderItemUpdated::class));

        $this->expectException(InvalidOrderItemIdException::class);
        $this->expectExceptionCode(422);
        $this->expectExceptionMessage('Order item id is invalid!');

        $this->updateOrderItems->perform(1, $this->items, new Admin());
    }

    public function testItThrowsExceptionWhenOrderDocumentIsNotFound(): void
    {
        $validStatuses = [
            OrderStatus::WAIT_CUSTOMER,
            OrderStatus::WAITING_FOR_PAY,
            OrderStatus::CONFIRMED
        ];
        $this->orderRepoMock->shouldReceive('findOrdersWithStatusAndId')
                            ->once()
                            ->with(1, $validStatuses)
                            ->andReturn($this->orderMock);

        $this->orderMock->shouldReceive('getOrderItems')
                        ->once()
                        ->withNoArgs()
                        ->andReturn(new ArrayCollection([
                            $this->orderItemMock,
                            $this->orderItemMock,
                        ]));

        $this->orderItemMock->shouldReceive('getId')
                            ->twice()
                            ->withNoArgs()
                            ->andReturn(1, 2);
        $this->orderItemMock->shouldReceive('getPrice')
                            ->twice()
                            ->withNoArgs()
                            ->andReturn(50000, 30000);
        $this->orderItemMock->shouldReceive('getQuantity')
                            ->twice()
                            ->withNoArgs()
                            ->andReturn(1, 2);
        $this->orderItemMock->shouldReceive('getGrandTotal')
                            ->twice()
                            ->withNoArgs()
                            ->andReturn(55000, 70000);
        $this->orderItemMock->shouldReceive('setSubtotal')
                            ->once()
                            ->with(50000)
                            ->andReturn($this->orderItemMock);
        $this->orderItemMock->shouldReceive('setSubtotal')
                            ->once()
                            ->with(60000)
                            ->andReturn($this->orderItemMock);
        $this->orderItemMock->shouldReceive('setGrandTotal')
                            ->once()
                            ->with(55000)
                            ->andReturn($this->orderItemMock);
        $this->orderItemMock->shouldReceive('setGrandTotal')
                            ->once()
                            ->with(54000)
                            ->andReturn($this->orderItemMock);
        $this->orderItemMock->shouldReceive('setQuantity')
                            ->once()
                            ->with(1)
                            ->andReturn($this->orderItemMock);
        $this->orderItemMock->shouldReceive('setQuantity')
                            ->once()
                            ->with(2)
                            ->andReturn($this->orderItemMock);

        $this->recalculateDocumentMock->expects('perform')
                                      ->with($this->orderMock)
                                      ->andThrows(new OrderDocumentNotFoundException());

        $this->dispatcherMock->shouldReceive('dispatch')
                             ->once()
                             ->with(Mockery::type(OrderItemUpdated::class));

        $this->expectException(OrderDocumentNotFoundException::class);
        $this->expectExceptionCode(404);
        $this->expectExceptionMessage('Order document is not found!');

        $this->updateOrderItems->perform(1, $this->items, new Admin());
    }

    public function testShouldUpdateItemsQuantityAndReleaseInventoryStockIfOrderIsInWaitCustomerState(): void
    {
        $validStatuses = [
            OrderStatus::WAIT_CUSTOMER,
            OrderStatus::WAITING_FOR_PAY,
            OrderStatus::CONFIRMED
        ];
        $this->orderRepoMock->shouldReceive('findOrdersWithStatusAndId')
                            ->once()
                            ->with(1, $validStatuses)
                            ->andReturn($this->orderMock);
        $this->orderMock->shouldReceive('getOrderItems')
                        ->once()
                        ->withNoArgs()
                        ->andReturn(new ArrayCollection([
                            $this->orderItemMock,
                            $this->orderItemMock,
                        ]));
        $this->orderMock->shouldReceive('getStatus')
                        ->twice()
                        ->withNoArgs()
                        ->andReturn(OrderStatus::WAIT_CUSTOMER);

        $this->orderItemMock->shouldReceive('getId')
                            ->twice()
                            ->withNoArgs()
                            ->andReturn(1, 2);
        $this->orderItemMock->shouldReceive('getPrice')
                            ->twice()
                            ->withNoArgs()
                            ->andReturn(50000, 30000);
        $this->orderItemMock->shouldReceive('getQuantity')
                            ->twice()
                            ->withNoArgs()
                            ->andReturn(1, 2);
        $this->orderItemMock->shouldReceive('getGrandTotal')
                            ->twice()
                            ->withNoArgs()
                            ->andReturn(55000, 70000);
        $this->orderItemMock->shouldReceive('setSubtotal')
                            ->once()
                            ->with(0)
                            ->andReturn($this->orderItemMock);
        $this->orderItemMock->shouldReceive('setSubtotal')
                            ->once()
                            ->with(90000)
                            ->andReturn($this->orderItemMock);
        $this->orderItemMock->shouldReceive('setGrandTotal')
                            ->once()
                            ->with(0)
                            ->andReturn($this->orderItemMock);
        $this->orderItemMock->shouldReceive('setGrandTotal')
                            ->once()
                            ->with(81000)
                            ->andReturn($this->orderItemMock);
        $this->orderItemMock->shouldReceive('setQuantity')
                            ->once()
                            ->with(0)
                            ->andReturn($this->orderItemMock);
        $this->orderItemMock->shouldReceive('setQuantity')
                            ->once()
                            ->with(3)
                            ->andReturn($this->orderItemMock);

        $this->orderItemMock->shouldReceive('getInventory')
                            ->twice()
                            ->withNoArgs()
                            ->andReturn($this->inventoryMock);

        $this->orderItemMock->shouldReceive('getOrder')
                            ->twice()
                            ->withNoArgs()
                            ->andReturn($this->orderMock);

        $this->orderMock->shouldReceive('getId')
                        ->once()
                        ->withNoArgs()
                        ->andReturn(59);

        $this->dispatcherMock->shouldReceive('dispatch')
                             ->times(4);

        $this->em->shouldReceive('flush')
                 ->once()
                 ->withNoArgs()
                 ->andReturn();

        $this->inventoryMock->shouldReceive('increaseStockCount')
                            ->once()
                            ->with(1)
                            ->andReturn($this->inventoryMock);
        $this->inventoryMock->shouldReceive('decreaseStockCount')
                            ->once()
                            ->with(1)
                            ->andReturn($this->inventoryMock);

        $this->recalculateDocumentMock->expects('perform')->with($this->orderMock)->andReturns();

        // Increment quantity of second product and assert that the OrderItemLog::create is being called
        $items                = $this->items;
        $items[0]['quantity'] = 0;
        $items[1]['quantity'] = 3;
        $this->logCreatorMock->shouldReceive('create')->twice();

        $this->walletPaymentHandler
            ->shouldReceive('handle')
            ->once()
            ->with($this->orderMock, TransferReason::UPDATE_ORDER_ITEM)
            ->andReturnNull();

        $result = $this->updateOrderItems->perform(1, $items, new Admin());

        self::assertEquals($this->orderMock, $result);
    }

    /**
     * @dataProvider  StatusProvider
     */
    public function testShouldUpdateItemsQuantityButNotReleaseInventoryStockIfOrderIsNotInWaitCustomerState(string $status): void
    {
        $validStatuses = [
            OrderStatus::WAIT_CUSTOMER,
            OrderStatus::WAITING_FOR_PAY,
            OrderStatus::CONFIRMED
        ];
        $this->orderRepoMock->shouldReceive('findOrdersWithStatusAndId')
                            ->once()
                            ->with(1, $validStatuses)
                            ->andReturn($this->orderMock);
        $this->orderMock->shouldReceive('getOrderItems')
                        ->once()
                        ->withNoArgs()
                        ->andReturn(new ArrayCollection([
                            $this->orderItemMock,
                            $this->orderItemMock,
                        ]));
        $this->orderMock->shouldReceive('getStatus')
                        ->twice()
                        ->withNoArgs()
                        ->andReturn($status);

        $this->orderItemMock->shouldReceive('getId')
                            ->twice()
                            ->withNoArgs()
                            ->andReturn(1, 2);
        $this->orderItemMock->shouldReceive('getPrice')
                            ->twice()
                            ->withNoArgs()
                            ->andReturn(50000, 30000);
        $this->orderItemMock->shouldReceive('getQuantity')
                            ->twice()
                            ->withNoArgs()
                            ->andReturn(1, 2);
        $this->orderItemMock->shouldReceive('getGrandTotal')
                            ->twice()
                            ->withNoArgs()
                            ->andReturn(55000, 70000);
        $this->orderItemMock->shouldReceive('setSubtotal')
                            ->once()
                            ->with(0)
                            ->andReturn($this->orderItemMock);
        $this->orderItemMock->shouldReceive('setSubtotal')
                            ->once()
                            ->with(90000)
                            ->andReturn($this->orderItemMock);
        $this->orderItemMock->shouldReceive('setGrandTotal')
                            ->once()
                            ->with(0)
                            ->andReturn($this->orderItemMock);
        $this->orderItemMock->shouldReceive('setGrandTotal')
                            ->once()
                            ->with(81000)
                            ->andReturn($this->orderItemMock);
        $this->orderItemMock->shouldReceive('setQuantity')
                            ->once()
                            ->with(0)
                            ->andReturn($this->orderItemMock);
        $this->orderItemMock->shouldReceive('setQuantity')
                            ->once()
                            ->with(3)
                            ->andReturn($this->orderItemMock);

        $this->orderItemMock->shouldReceive('getInventory')
                            ->once()
                            ->withNoArgs()
                            ->andReturn($this->inventoryMock);

        $this->orderItemMock->shouldReceive('getOrder')
                            ->twice()
                            ->withNoArgs()
                            ->andReturn($this->orderMock);

        $this->orderMock->shouldReceive('getId')
                        ->once()
                        ->withNoArgs()
                        ->andReturn(59);

        $this->dispatcherMock->shouldReceive('dispatch')
                             ->times(4);

        $this->em->shouldReceive('flush')
                 ->once()
                 ->withNoArgs()
                 ->andReturn();

        $this->inventoryMock->shouldReceive('decreaseStockCount')
                            ->once()
                            ->with(1)
                            ->andReturn($this->inventoryMock);

        $this->recalculateDocumentMock->expects('perform')->with($this->orderMock)->andReturns();

        // Increment quantity of second product and assert that the OrderItemLog::create is being called
        $items                = $this->items;
        $items[0]['quantity'] = 0;
        $items[1]['quantity'] = 3;
        $this->logCreatorMock->shouldReceive('create')->twice();

        $this->walletPaymentHandler
            ->shouldReceive('handle')
            ->once()
            ->with($this->orderMock, TransferReason::UPDATE_ORDER_ITEM)
            ->andReturnNull();

        $result = $this->updateOrderItems->perform(1, $items, new Admin());

        self::assertEquals($this->orderMock, $result);
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

    public function testItDoNothingIfNoneOfItemsChanged(): void
    {
        $validStatuses = [
            OrderStatus::WAIT_CUSTOMER,
            OrderStatus::WAITING_FOR_PAY,
            OrderStatus::CONFIRMED
        ];
        $this->orderRepoMock->shouldReceive('findOrdersWithStatusAndId')
                            ->once()
                            ->with(1, $validStatuses)
                            ->andReturn($this->orderMock);

        $this->orderMock->shouldReceive('getOrderItems')
                        ->once()
                        ->withNoArgs()
                        ->andReturn(new ArrayCollection([
                            $this->orderItemMock,
                            $this->orderItemMock,
                        ]));

        $this->orderItemMock->shouldReceive('getId')
                            ->twice()
                            ->withNoArgs()
                            ->andReturn(1, 2);
        $this->orderItemMock->shouldReceive('getPrice')
                            ->twice()
                            ->withNoArgs()
                            ->andReturn(55000, 27000);
        $this->orderItemMock->shouldReceive('getQuantity')
                            ->twice()
                            ->withNoArgs()
                            ->andReturn(1, 2);

        $this->em->shouldNotReceive('flush');

        $this->inventoryMock->shouldNotReceive('increaseStockCount');

        $this->logCreatorMock->shouldNotReceive('create');

        $result = $this->updateOrderItems->perform(1, $this->items, new Admin());

        self::assertEquals($this->orderMock, $result);
    }
}
