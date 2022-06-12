<?php

namespace App\Tests\Unit\Service\Order\Stages;

use App\Dictionary\OrderPaymentMethod;
use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Category;
use App\Entity\City;
use App\Entity\CustomerAddress;
use App\Entity\District;
use App\Entity\Inventory;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use App\Entity\ProductVariant;
use App\Service\Order\CreateOrderPayload;
use App\Service\Order\Stages\StoreOrderItemStage;
use App\Service\OrderItemLog\OrderItemLogCreator;
use Doctrine\ORM\EntityManagerInterface;
use LongitudeOne\Spatial\PHP\Types\AbstractPoint;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class StoreOrderItemStageTest extends MockeryTestCase
{
    private CreateOrderPayload $storeOrderPayload;

    private StoreOrderItemStage $storeOrderItemStage;

    private Cart $cart;

    private $manager;

    private OrderItemLogCreator $orderItemLogCreator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cart = $this->createFakeCartWithCartItem();

        $this->manager = m::mock(EntityManagerInterface::class);
        $this->storeOrderPayload = new CreateOrderPayload(
            $this->manager,
            $this->cart,
            $this->createFakeCustomerAddress(),
            OrderPaymentMethod::OFFLINE,
            [],
            [],
            false,
            false
        );

        $this->storeOrderPayload->setOrder(new Order());

        $this->orderItemLogCreator = m::mock(OrderItemLogCreator::class);

        $this->storeOrderItemStage = new StoreOrderItemStage($this->orderItemLogCreator);
    }

    protected function tearDown(): void
    {
        unset(
            $this->manager,
            $this->cart,
            $this->storeOrderPayload,
            $this->orderItemLogCreator,
            $this->storeOrderItemStage
        );
    }

    public function testItCanStoreOrderItemSuccessfully(): void
    {
        $this->manager->shouldReceive('persist')
            ->once()
            ->with(m::type(OrderItem::class))
            ->andReturn();

        $this->orderItemLogCreator->shouldReceive('create')
            ->once()
            ->with(m::type(OrderItem::class), m::type('int'), m::type('int'))
        ;

        $orderItems = $this->storeOrderItemStage->__invoke($this->storeOrderPayload)->getOrder()->getOrderItems();
        $cartItems = $this->cart->getCartItems()->toArray();

        self::assertNotNull($orderItems);

        foreach ($orderItems as $key => $orderItem) {
            self::assertEquals($orderItem->getSubtotal(), $cartItems[$key]->getSubtotal());
            self::assertEquals($orderItem->getGrandTotal(), $cartItems[$key]->getGrandTotal());
            self::assertEquals($orderItem->getInventory(), $cartItems[$key]->getInventory());
            self::assertEquals($orderItem->getPrice(), $cartItems[$key]->getPrice());
            self::assertEquals($orderItem->getQuantity(), $cartItems[$key]->getQuantity());
            self::assertEquals($orderItem->getLeadTime(), $cartItems[$key]->getInventory()->getLeadTime());
        }
    }

    private function createFakeCartWithCartItem(): Cart
    {
        $cartItem = (new CartItem())
            ->setGrandTotal(10)
            ->setSubtotal(10)
            ->setQuantity(1)
            ->setPrice(10)
            ->setFinalPrice(10)
            ->setInventory((new Inventory())
            ->setVariant((new ProductVariant())
            ->setProduct((new Product())
            ->setCategory((new Category())
            ->setCommission(10)))));

        return (new Cart())
            ->addCartItem($cartItem)
            ->setGrandTotal(10)
            ->setSubtotal(20);
    }

    private function createFakeCustomerAddress(): CustomerAddress
    {
        return (new CustomerAddress())
            ->setCity(new City())
            ->setCoordinates(m::mock(AbstractPoint::class))
            ->setDistrict(new District())
            ->setFamily('Family')
            ->setName('Name')
            ->setNationalCode(1234567899)
            ->setFullAddress('FullAddress')
            ->setNumber(210)
            ->setMobile('0912123456789')
            ->setPostalCode(1234567899)
            ->setUnit(2);
    }
}
