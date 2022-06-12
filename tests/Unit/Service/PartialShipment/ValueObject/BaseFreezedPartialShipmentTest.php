<?php

namespace App\Tests\Unit\Service\PartialShipment\ValueObject;

use App\Entity\Inventory;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\OrderShipment;
use App\Entity\ShippingCategory;
use App\Entity\ShippingMethod;
use App\Entity\ShippingPeriod;
use App\Service\PartialShipment\ValueObject\BaseFreezedPartialShipment;
use App\Service\PartialShipment\ValueObject\PartialShipmentItem;
use App\Service\PartialShipment\ValueObject\PartialShipmentPrice;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class BaseFreezedPartialShipmentTest
 */
class BaseFreezedPartialShipmentTest extends MockeryTestCase
{
    public function testGetters(): void
    {
        $shipmentItem = \Mockery::mock(PartialShipmentItem::class);
        $shipmentItem->shouldReceive('getShippingCategory')
                     ->once()
                     ->withNoArgs()
                     ->andReturn(\Mockery::mock(ShippingCategory::class));

        $shippingMethod = new ShippingMethod();
        $price          = new PartialShipmentPrice(20000, 20000);
        $deliveryDate   = new \DateTime();
        $shipmentItems  = [$shipmentItem];
        $title          = 'title';

        $partialShipment = new BaseFreezedPartialShipment(
            $shipmentItems,
            $shippingMethod,
            $price,
            $deliveryDate,
            $title
        );

        $mapping = [
            'getShipmentItems'  => $shipmentItems,
            'getShippingMethod' => $shippingMethod,
            'getPrice'          => $price,
            'getDeliveryDate'   => $deliveryDate,
            'getTitle'          => $title,
        ];

        foreach ($mapping as $method => $value) {
            self::assertSame($value, $partialShipment->$method());
        }
    }

    public function testItCreateAndReturnOrderShipmentWithShippingPeriod(): void
    {
        $shippingMethod  = \Mockery::mock(ShippingMethod::class);
        $price           = new PartialShipmentPrice(20000, 20000);
        $deliveryDate    = new \DateTime();
        $shipmentItems   = $this->getShipmentItems();
        $order           = $this->getOrder();
        $partialShipment = new BaseFreezedPartialShipment(
            $shipmentItems,
            $shippingMethod,
            $price,
            $deliveryDate,
            'title'
        );

        self::assertSame($order, $partialShipment->toOrderShipment($order)->getOrder());
    }

    public function testItCreateAndReturnOrderShipmentWithoutShippingPeriod(): void
    {
        $shippingMethod  = \Mockery::mock(ShippingMethod::class);
        $price           = new PartialShipmentPrice(20000, 20000);
        $deliveryDate    = new \DateTime();
        $shipmentItems   = $this->getShipmentItems();
        $order           = $this->getOrder();
        $partialShipment = new BaseFreezedPartialShipment(
            $shipmentItems,
            $shippingMethod,
            $price,
            $deliveryDate,
            'title'
        );

        $orderShipment = $partialShipment->toOrderShipment($order);

        self::assertSame($order, $orderShipment->getOrder());
    }

    public function getShipmentItems(): array
    {
        $shipmentItems = array_map(\Closure::fromCallable([$this, 'getShipmentItem']), range(1, 10));
        $shipmentItems[0]->shouldReceive('getShippingCategory')
                         ->once()
                         ->withNoArgs()
                         ->andReturn(\Mockery::mock(ShippingCategory::class));

        return $shipmentItems;
    }

    public function getShipmentItem($inventoryId): PartialShipmentItem
    {
        $shipmentItem = \Mockery::mock(PartialShipmentItem::class);
        $shipmentItem->shouldReceive('getInventory')->once()->andReturn($this->getInventory($inventoryId));

        return $shipmentItem;
    }

    private function getInventory($id): Inventory
    {
        $inventory = \Mockery::mock(Inventory::class);
        $inventory->shouldReceive('getId')->once()->withNoArgs()->andReturn($id);

        return $inventory;
    }

    private function getOrder(): Order
    {
        $orderItems = $this->getOrderItems();

        $order = \Mockery::mock(Order::class);
        $order->shouldReceive('getOrderItems')->once()->withNoArgs()->andReturn($orderItems);

        return $order;
    }

    private function getOrderItems(): ArrayCollection
    {
        return new ArrayCollection(array_map(\Closure::fromCallable([$this, 'getOrderItem']), range(1, 10)));
    }

    private function getOrderItem($id): OrderItem
    {
        $orderItem = \Mockery::mock(OrderItem::class);
        $orderItem->shouldReceive('getInventory')
                  ->once()
                  ->withNoArgs()
                  ->andReturn($this->getInventory($id));

        $orderItem->shouldReceive('setOrderShipment')
                  ->once()
                  ->with(\Mockery::type(OrderShipment::class))
                  ->andReturnSelf();

        return $orderItem;
    }
}
