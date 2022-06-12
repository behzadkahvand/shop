<?php

namespace App\Tests\Unit\Service\Order\Stages;

use App\Dictionary\OrderPaymentMethod;
use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\City;
use App\Entity\CustomerAddress;
use App\Entity\District;
use App\Entity\Inventory;
use App\Entity\Order;
use App\Entity\OrderDocument;
use App\Entity\OrderShipment;
use App\Service\Order\CreateOrderPayload;
use App\Service\Order\Stages\StoreOrderShipmentStage;
use App\Service\OrderShipment\OrderShipmentServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use LongitudeOne\Spatial\PHP\Types\AbstractPoint;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class StoreOrderShipmentStageTest extends MockeryTestCase
{
    private CreateOrderPayload $storeOrderPayload;

    private StoreOrderShipmentStage $storeOrderShipmentStage;

    /**
     * @var OrderShipmentServiceInterface|m\LegacyMockInterface|m\MockInterface
     */
    private $orderShipmentService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderShipmentService = m::mock(OrderShipmentServiceInterface::class);

        $this->storeOrderPayload = new CreateOrderPayload(
            m::mock(EntityManagerInterface::class),
            $this->createFakeCartWithCartItem(),
            $this->createFakeCustomerAddress(),
            OrderPaymentMethod::OFFLINE,
            [],
            [],
            false,
            false
        );

        $order = (new Order())
            ->setOrderDocument(new OrderDocument())
            ->setSubtotal(50)
            ->setGrandTotal(50);

        $this->storeOrderPayload->setOrder($order);

        $this->storeOrderShipmentStage = new StoreOrderShipmentStage($this->orderShipmentService);
    }

    protected function tearDown(): void
    {
        unset($this->storeOrderShipmentStage, $this->storeOrderPayload);

        $this->orderShipmentService = null;
    }

    public function testItCanStoreOrderShipmentSuccessfully(): void
    {
        $shipmentSubTotal = 10;
        $shipmentGrandTotal = 10;

        $this->orderShipmentService->shouldReceive('getShipmentObjects')
            ->once()
            ->with(
                $this->storeOrderPayload->getCart(),
                $this->storeOrderPayload->getOrder(),
                $this->storeOrderPayload->getSelectedShipments(),
                $this->storeOrderPayload->getCustomerAddress(),
            )
            ->andReturn([
                (new OrderShipment())->setGrandTotal($shipmentGrandTotal)->setSubTotal($shipmentSubTotal),
                (new OrderShipment())->setGrandTotal($shipmentGrandTotal)->setSubTotal($shipmentSubTotal),
            ]);

        $orderSubTotal = $this->storeOrderPayload->getOrder()->getSubtotal();
        $orderGrandTotal = $this->storeOrderPayload->getOrder()->getGrandTotal();

        $order = $this->storeOrderShipmentStage->__invoke($this->storeOrderPayload)->getOrder();
        $shipments = $order->getShipments();

        self::assertEquals(2, $shipments->count());

        foreach ($shipments as $shipment) {
            $orderSubTotal += $shipment->getSubTotal();
            $orderGrandTotal += $shipment->getGrandTotal();
        }

        self::assertEquals($orderSubTotal, $order->getSubtotal());
        self::assertEquals($orderGrandTotal, $order->getGrandTotal());
    }

    private function createFakeCartWithCartItem(): Cart
    {
        $cart = new Cart();
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
