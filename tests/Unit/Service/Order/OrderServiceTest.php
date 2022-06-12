<?php

namespace App\Tests\Unit\Service\Order;

use App\Dictionary\OrderPaymentMethod;
use App\Dictionary\OrderStatus;
use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Customer;
use App\Entity\CustomerAddress;
use App\Entity\Inventory;
use App\Entity\Order;
use App\Entity\OrderAffiliator;
use App\Entity\OrderShipment;
use App\Events\Order\OrderRegisteredEvent;
use App\Messaging\Messages\Command\Order\SendOrderAffiliatorPurchaseRequest;
use App\Service\Cart\Exceptions\CartNotFoundException;
use App\Service\Order\CreateOrderPayload;
use App\Service\Order\OrderService;
use App\Service\Pipeline\PipelineStageInterface;
use App\Service\Promotion\PromotionProcessorInterface;
use App\Tests\Unit\BaseUnitTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Throwable;

class OrderServiceTest extends BaseUnitTestCase
{
    private LegacyMockInterface|EntityManagerInterface|MockInterface|null $entityManager;

    private ?OrderService $orderService;

    private ?Customer $customer;

    private ?CustomerAddress $customerAddress;

    private ?array $shipments;

    private ?Cart $cart;

    private EventDispatcherInterface|LegacyMockInterface|MockInterface|null $eventDispatcher;

    private LegacyMockInterface|PipelineStageInterface|MockInterface|null $pipelineStage;

    private Order|LegacyMockInterface|MockInterface|null $order;

    protected LegacyMockInterface|MockInterface|OrderAffiliator|null $orderAffiliator;

    private LegacyMockInterface|PromotionProcessorInterface|MockInterface|null $promotionProcessor;

    private LegacyMockInterface|MessageBusInterface|MockInterface|null $messageBus;

    protected function setUp(): void
    {
        $this->customer        = new Customer();
        $this->customerAddress = new CustomerAddress();
        $this->cart            = $this->createFakeCartWithCartItem();
        $this->shipments       = [];

        $this->order              = Mockery::mock(Order::class);
        $this->orderAffiliator    = Mockery::mock(OrderAffiliator::class);
        $this->entityManager      = Mockery::mock(EntityManagerInterface::class);
        $this->eventDispatcher    = Mockery::mock(EventDispatcherInterface::class);
        $this->promotionProcessor = Mockery::mock(PromotionProcessorInterface::class);
        $this->pipelineStage      = Mockery::mock(PipelineStageInterface::class);
        $this->messageBus         = Mockery::mock(MessageBusInterface::class);

        $this->orderService = new OrderService(
            $this->entityManager,
            $this->eventDispatcher,
            [$this->pipelineStage],
            $this->promotionProcessor,
            $this->messageBus
        );
    }

    /**
     * @throws Throwable
     */
    public function testStoreOrderSuccessWithOnlineOrder(): void
    {
        $this->customer->setCart($this->cart);
        $orderShipment = new OrderShipment();
        $orderShipment->setGrandTotal(10);
        $orderShipment->setSubTotal(10);
        $this->customerAddress->setCustomer($this->customer);

        $this->entityManager->shouldReceive('beginTransaction')
                            ->once()
                            ->andReturn();

        $this->entityManager->shouldReceive('commit')
                            ->once()
                            ->andReturn();

        $this->entityManager->shouldReceive('persist')
                            ->andReturn();
        $this->entityManager->shouldReceive('flush')
                            ->andReturn();

        $this->eventDispatcher->shouldReceive('dispatch')
                              ->once()
                              ->with(Mockery::type(OrderRegisteredEvent::class))
                              ->andReturn();

        $this->pipelineStage->shouldReceive('__invoke')
                            ->once()
                            ->with(Mockery::type(CreateOrderPayload::class))
                            ->andReturnUsing(function (CreateOrderPayload $payload) {
                                return $payload->setOrder($this->order);
                            });

        $this->order->shouldReceive('getStatus')
                    ->once()
                    ->withNoArgs()
                    ->andReturn(OrderStatus::WAITING_FOR_PAY);
        $this->order->shouldReceive('getAffiliator')
                    ->once()
                    ->withNoArgs()
                    ->andReturn($this->orderAffiliator);

        $this->orderService->store(
            $this->customer,
            OrderPaymentMethod::ONLINE,
            $this->shipments,
            $this->customerAddress,
            [],
            true
        );

        self::assertTrue(true);
    }

    /**
     * @throws Throwable
     */
    public function testStoreOrderSuccessWithOfflineOrderAndOrderHasAffiliator(): void
    {
        $this->customer->setCart($this->cart);
        $orderShipment = new OrderShipment();
        $orderShipment->setGrandTotal(10);
        $orderShipment->setSubTotal(10);
        $this->customerAddress->setCustomer($this->customer);

        $this->entityManager->shouldReceive('beginTransaction')
                            ->once()
                            ->andReturn();

        $this->entityManager->shouldReceive('commit')
                            ->once()
                            ->andReturn();

        $this->entityManager->shouldReceive('persist')
                            ->andReturn();
        $this->entityManager->shouldReceive('flush')
                            ->andReturn();

        $this->eventDispatcher->shouldReceive('dispatch')
                              ->once()
                              ->with(Mockery::type(OrderRegisteredEvent::class))
                              ->andReturn();

        $this->pipelineStage->shouldReceive('__invoke')
                            ->once()
                            ->with(Mockery::type(CreateOrderPayload::class))
                            ->andReturnUsing(function (CreateOrderPayload $payload) {
                                return $payload->setOrder($this->order);
                            });

        $this->order->shouldReceive('getStatus')
                    ->once()
                    ->withNoArgs()
                    ->andReturn(OrderStatus::WAIT_CUSTOMER);
        $this->order->shouldReceive('getAffiliator')
                    ->once()
                    ->withNoArgs()
                    ->andReturn($this->orderAffiliator);
        $this->order->shouldReceive('getId')
                    ->once()
                    ->withNoArgs()
                    ->andReturn(43);

        $this->messageBus->shouldReceive('dispatch')
                         ->once()
                         ->with(Mockery::type(SendOrderAffiliatorPurchaseRequest::class))
                         ->andReturn(new Envelope(new stdClass()));

        $this->orderService->store(
            $this->customer,
            OrderPaymentMethod::OFFLINE,
            $this->shipments,
            $this->customerAddress,
            [],
            true
        );

        self::assertTrue(true);
    }

    public function testStoreOrderSuccessWithOfflineOrderAndOrderHasNoAffiliator(): void
    {
        $this->customer->setCart($this->cart);
        $orderShipment = new OrderShipment();
        $orderShipment->setGrandTotal(10);
        $orderShipment->setSubTotal(10);
        $this->customerAddress->setCustomer($this->customer);

        $this->entityManager->shouldReceive('beginTransaction')
                            ->once()
                            ->andReturn();

        $this->entityManager->shouldReceive('commit')
                            ->once()
                            ->andReturn();

        $this->entityManager->shouldReceive('persist')
                            ->andReturn();
        $this->entityManager->shouldReceive('flush')
                            ->andReturn();

        $this->eventDispatcher->shouldReceive('dispatch')
                              ->once()
                              ->with(Mockery::type(OrderRegisteredEvent::class))
                              ->andReturn();

        $this->pipelineStage->shouldReceive('__invoke')
                            ->once()
                            ->with(Mockery::type(CreateOrderPayload::class))
                            ->andReturnUsing(function (CreateOrderPayload $payload) {
                                return $payload->setOrder($this->order);
                            });

        $this->order->shouldReceive('getAffiliator')
                    ->once()
                    ->withNoArgs()
                    ->andReturnNull();

        $this->orderService->store(
            $this->customer,
            OrderPaymentMethod::OFFLINE,
            $this->shipments,
            $this->customerAddress,
            [],
            true
        );

        self::assertTrue(true);
    }

    /**
     * @throws Throwable
     */
    public function testStoreOrderFailWithCartNotFound(): void
    {
        $this->expectException(CartNotFoundException::class);

        $this->orderService->store(
            $this->customer,
            OrderPaymentMethod::ONLINE,
            $this->shipments,
            $this->customerAddress
        );
    }

    private function createFakeCartWithCartItem(): Cart
    {
        $cart     = new Cart();
        $cartItem = new CartItem();
        $cartItem->setGrandTotal(10);
        $cartItem->setSubtotal(10);
        $cartItem->setQuantity(1);
        $cartItem->setPrice(10);
        $cartItem->setInventory(new Inventory());
        $cart->addCartItem($cartItem);
        $cart->setGrandTotal(10);
        $cart->setSubtotal(20);

        return $cart;
    }
}
