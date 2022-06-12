<?php

namespace App\Tests\Unit\Service\PartialShipment\ValueObject;

use App\Entity\Inventory;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\OrderShipment;
use App\Entity\ShippingCategory;
use App\Entity\ShippingMethod;
use App\Service\PartialShipment\ValueObject\FreezedPartialShipment;
use App\Service\PartialShipment\ValueObject\PartialShipmentItem;
use App\Service\PartialShipment\ValueObject\PartialShipmentPrice;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class FreezedPartialShipmentTest
 */
final class FreezedPartialShipmentTest extends MockeryTestCase
{
    public function testGetters(): void
    {
        $shipmentItem = \Mockery::mock(PartialShipmentItem::class);
        $shipmentItem->shouldReceive('getShippingCategory')
                     ->once()
                     ->withNoArgs()
                     ->andReturn(\Mockery::mock(ShippingCategory::class));

        $shippingMethod        = new ShippingMethod();
        $price                 = new PartialShipmentPrice(20000, 20000);
        $deliveryDate          = new \DateTime();
        $inventories           = [$shipmentItem];
        $title                 = 'title';
        $categoryDeliveryRange = [1, 2];

        $partialShipment = new FreezedPartialShipment(
            $inventories,
            $shippingMethod,
            $price,
            $deliveryDate,
            $title,
            'description',
            $categoryDeliveryRange
        );

        self::assertSame($categoryDeliveryRange, $partialShipment->getCategoryDeliveryRange());
    }

    public function testItCreateAndReturnOrderShipmentWithShippingPeriod(): void
    {
        $shippingMethod        = \Mockery::mock(ShippingMethod::class);
        $price                 = new PartialShipmentPrice(20000, 20000);
        $deliveryDate          = new \DateTime();
        $inventories           = $this->getPartialShipmentItems();
        $order                 = $this->getOrder();
        $categoryDeliveryRange = [1, 2];
        $partialShipment       = new FreezedPartialShipment(
            $inventories,
            $shippingMethod,
            $price,
            $deliveryDate,
            'title',
            'description',
            $categoryDeliveryRange
        );

        $orderShipment = $partialShipment->toOrderShipment($order);

        self::assertSame($order, $orderShipment->getOrder());
        self::assertSame($categoryDeliveryRange, $orderShipment->getCategoryDeliveryRange());
    }

    public function testItCreateAndReturnOrderShipmentWithoutShippingPeriod(): void
    {
        $shippingMethod        = \Mockery::mock(ShippingMethod::class);
        $price                 = new PartialShipmentPrice(20000, 20000);
        $deliveryDate          = new \DateTime();
        $inventories           = $this->getPartialShipmentItems();
        $order                 = $this->getOrder();
        $categoryDeliveryRange = [1, 2];
        $partialShipment       = new FreezedPartialShipment(
            $inventories,
            $shippingMethod,
            $price,
            $deliveryDate,
            'title',
            'description',
            $categoryDeliveryRange
        );

        $orderShipment = $partialShipment->toOrderShipment($order);

        self::assertSame($order, $orderShipment->getOrder());
        self::assertNull($orderShipment->getPeriod());
        self::assertSame($categoryDeliveryRange, $orderShipment->getCategoryDeliveryRange());
    }

    private function getPartialShipmentItems(): array
    {
        $shipmentItems = array_map(\Closure::fromCallable([$this, 'getPartialShipmentItem']), range(1, 10));
        $shipmentItems[0]->shouldReceive('getShippingCategory')
                         ->once()
                         ->withNoArgs()
                         ->andReturn(\Mockery::mock(ShippingCategory::class));

        return $shipmentItems;
    }

    private function getPartialShipmentItem($inventoryId): PartialShipmentItem
    {
        $shipmentItem = \Mockery::mock(PartialShipmentItem::class);
        $shipmentItem->shouldReceive('getInventory')
                     ->once()
                     ->withNoArgs()
                     ->andReturn($this->getInventory($inventoryId));

        return $shipmentItem;
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

    private function getInventory(int $id): Inventory
    {
        $inventory = \Mockery::mock(Inventory::class);
        $inventory->shouldReceive('getId')->once()->withNoArgs()->andReturn($id);

        return $inventory;
    }
}
