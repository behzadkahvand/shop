<?php

namespace App\Tests\Unit\Service\Order\Stages;

use App\Dictionary\OrderPaymentMethod;
use App\Dictionary\OrderStatus;
use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Customer;
use App\Entity\CustomerAddress;
use App\Entity\Inventory;
use App\Entity\Order;
use App\Entity\OrderDocument;
use App\Service\Order\CreateOrderPayload;
use App\Service\Order\OrderIdentifierService;
use App\Service\Order\Stages\StoreOrderStage;
use App\Tests\Unit\BaseUnitTestCase;
use Doctrine\ORM\EntityManagerInterface;
use LongitudeOne\Spatial\PHP\Types\Geometry\Point;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;

class StoreOrderStageTest extends BaseUnitTestCase
{
    private const IDENTIFIER = 'IDENTIFIER_TEST';

    private LegacyMockInterface|EntityManagerInterface|MockInterface|null $manager;

    private OrderIdentifierService|LegacyMockInterface|MockInterface|null $identifierService;

    private ?StoreOrderStage $storeOrderStage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->manager           = Mockery::mock(EntityManagerInterface::class);
        $this->identifierService = Mockery::mock(OrderIdentifierService::class);

        $this->storeOrderStage = new StoreOrderStage($this->identifierService);
    }

    public function testGetPriorityAndTag(): void
    {
        self::assertEquals(100, StoreOrderStage::getPriority());
        self::assertEquals('app.pipeline_stage.order_processing', StoreOrderStage::getTag());
    }

    public function testItCanStoreOrderSuccessfully(): void
    {
        $this->manager->shouldReceive('persist')
                      ->once()
                      ->with(Mockery::type(Order::class))
                      ->andReturn();

        $this->manager->shouldReceive('persist')
                      ->once()
                      ->with(Mockery::type(OrderDocument::class))
                      ->andReturn();

        $this->manager->shouldReceive('flush')
                      ->once()
                      ->andReturn();

        $this->identifierService->shouldReceive('generateIdentifier')
                                ->once()
                                ->with(Mockery::type(Order::class))
                                ->andReturn(self::IDENTIFIER);

        $payload = new CreateOrderPayload(
            $this->manager,
            $this->createFakeCartWithCartItem(),
            $this->createFakeCustomerAddress(),
            OrderPaymentMethod::OFFLINE,
            [],
            [],
            false,
            false
        );

        $order = $this->storeOrderStage->__invoke($payload)->getOrder();

        self::assertNotNull($order->getCustomer());
        self::assertNotNull($order->getGrandTotal());
        self::assertNotNull($order->getSubtotal());
        self::assertEquals(OrderPaymentMethod::OFFLINE, $order->getPaymentMethod());
        self::assertNotNull($order->getOrderDocument());
        self::assertEquals(self::IDENTIFIER, $order->getIdentifier());
        self::assertEquals(OrderStatus::NEW, $order->getStatus());
        self::assertEquals(0, $order->getBalanceAmount());
        self::assertFalse($order->getIsLegal());
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

    private function createFakeCustomerAddress(): CustomerAddress
    {
        return (new CustomerAddress())->setCustomer(new Customer())
                                      ->setCoordinates(new Point(51.25115, 35.51455));
    }
}
