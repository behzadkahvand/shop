<?php

namespace App\Tests\Unit\Service\Order\ReturnRequest;

use App\Dictionary\SellerOrderItemStatus;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\ReturnRequest;
use App\Entity\ReturnRequestItem;
use App\Entity\SellerOrderItem;
use App\Exceptions\Order\ReturnRequest\InvalidReturnRequestException;
use App\Service\Order\ReturnRequest\Validator\ReturnRequestValidator;
use App\Tests\Unit\BaseUnitTestCase;
use Mockery;
use Mockery\Mock;

class ReturnRequestValidatorTest extends BaseUnitTestCase
{
    private ReturnRequestValidator|null $sut;
    private ReturnRequest|null $returnRequest;
    private ReturnRequestItem|null $item_1;
    private ReturnRequestItem|null $item_2;
    private Order|null $order;
    private SellerOrderItem|null $sellerOrderItem_1;
    private SellerOrderItem|null $sellerOrderItem_2;
    private Mock|OrderItem|null $orderItem_1;
    private Mock|OrderItem|null $orderItem_2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->returnRequest = new ReturnRequest();
        $this->item_1 = new ReturnRequestItem();
        $this->item_2 = new ReturnRequestItem();
        $this->order = new Order();
        $this->orderItem_1 = Mockery::mock(OrderItem::class)->makePartial();
        $this->orderItem_2 = Mockery::mock(OrderItem::class)->makePartial();
        $this->sellerOrderItem_1 = new SellerOrderItem();
        $this->sellerOrderItem_2 = new SellerOrderItem();
        $this->order->addOrderItem($this->orderItem_1);
        $this->order->addOrderItem($this->orderItem_2);
        $this->orderItem_1->setSellerOrderItem($this->sellerOrderItem_1);
        $this->orderItem_2->setSellerOrderItem($this->sellerOrderItem_2);
        $this->orderItem_1->setId(1);
        $this->orderItem_2->setId(2);
        $this->returnRequest->addItem($this->item_1);
        $this->returnRequest->addItem($this->item_2);
        $this->returnRequest->setOrder($this->order);
        $this->item_1->setOrderItem($this->orderItem_1);
        $this->item_2->setOrderItem($this->orderItem_2);
        $this->sut = new ReturnRequestValidator();
    }

    /**
     * @dataProvider statusProvider
     */
    public function testShouldThrowExceptionIfAllItemsAreNotDelivered(string $status): void
    {
        $this->sellerOrderItem_1->setStatus(SellerOrderItemStatus::DELIVERED);
        $this->sellerOrderItem_2->setStatus($status);

        $this->expectException(InvalidReturnRequestException::class);
        $this->expectErrorMessage('Only delivered items can be returned');

        $this->sut->validate($this->returnRequest);
    }
    public function statusProvider()
    {
        $statuses = SellerOrderItemStatus::toArray();
        unset($statuses[SellerOrderItemStatus::DELIVERED]);
        foreach ($statuses as $status) {
            yield [$status];
        }
    }

    public function testShouldThrowExceptionIfAllOrderItemsDoNotBelongToSpecifiedOrder(): void
    {
        $this->sellerOrderItem_1->setStatus(SellerOrderItemStatus::DELIVERED);
        $this->sellerOrderItem_2->setStatus(SellerOrderItemStatus::DELIVERED);

        $anotherOrder = new Order();
        $anotherOrderItem = new OrderItem();
        $anotherSellerOrderItem = new SellerOrderItem();
        $anotherSellerOrderItem->setStatus(SellerOrderItemStatus::DELIVERED);
        $anotherOrderItem->setSellerOrderItem($anotherSellerOrderItem);
        $anotherOrderItem->setId(3);
        $anotherOrder->addOrderItem($anotherOrderItem);
        $invalidItem = new ReturnRequestItem();
        $invalidItem->setOrderItem($anotherOrderItem);
        $this->returnRequest->addItem($invalidItem);

        $this->expectException(InvalidReturnRequestException::class);
        $this->expectErrorMessage('All items should belong to specified order.');

        $this->sut->validate($this->returnRequest);
    }

    public function testShouldThrowExceptionIfRequestedQuantityIsGreaterThanOrderItemQuantity(): void
    {
        $this->sellerOrderItem_1->setStatus(SellerOrderItemStatus::DELIVERED);
        $this->sellerOrderItem_2->setStatus(SellerOrderItemStatus::DELIVERED);

        $orderItem_2_id = 2;
        $orderItem_1_quantity = 3;
        $orderItem_2_quantity = 4;
        $orderItem_1_returns_count = 1;
        $orderItem_2_returns_count = 2;
        $returnRequestItem_1_quantity = $orderItem_1_quantity - $orderItem_1_returns_count;
        $returnRequestItem_2_quantity = $orderItem_2_quantity - $orderItem_2_returns_count + 1; // this one is invalid
        $this->orderItem_1->setQuantity($orderItem_1_quantity);
        $this->orderItem_2->setQuantity($orderItem_2_quantity);
        $this->item_1->setQuantity($returnRequestItem_1_quantity);
        $this->item_2->setQuantity($returnRequestItem_2_quantity);
        $this->orderItem_1->shouldReceive('getReturnItemsCount')->once()->withNoArgs()->andReturn($orderItem_1_returns_count);
        $this->orderItem_2->shouldReceive('getReturnItemsCount')->once()->withNoArgs()->andReturn($orderItem_2_returns_count);
        $this->orderItem_2->shouldReceive('getId')->twice()->withNoArgs()->andReturn($orderItem_2_id);

        $this->expectException(InvalidReturnRequestException::class);
        $this->expectErrorMessage(
            'Total return quantities is greater than total item quantities for order item with id: ' . $orderItem_2_id
        );

        $this->sut->validate($this->returnRequest);
    }

    public function testShouldThrowExceptionIfThereIsDuplicateItemsInRequest(): void
    {
        $this->sellerOrderItem_1->setStatus(SellerOrderItemStatus::DELIVERED);
        $this->sellerOrderItem_2->setStatus(SellerOrderItemStatus::DELIVERED);

        $item = new ReturnRequestItem();
        $item->setOrderItem($this->orderItem_2);
        $this->returnRequest->addItem($item);

        $this->expectException(InvalidReturnRequestException::class);
        $this->expectErrorMessage(
            'Multiple request items for one order item is not allowed'
        );

        $this->sut->validate($this->returnRequest);
    }

    public function testShouldNotThrowExceptionIfEveryThingIsCorrect(): void
    {
        $this->sellerOrderItem_1->setStatus(SellerOrderItemStatus::DELIVERED);
        $this->sellerOrderItem_2->setStatus(SellerOrderItemStatus::DELIVERED);

        $this->orderItem_1->setQuantity(3);
        $this->orderItem_2->setQuantity(3);
        $this->item_1->setQuantity(1);
        $this->item_2->setQuantity(1);
        $this->orderItem_1->shouldReceive('getReturnItemsCount')->once()->withNoArgs()->andReturn(0);
        $this->orderItem_2->shouldReceive('getReturnItemsCount')->once()->withNoArgs()->andReturn(0);

        $this->sut->validate($this->returnRequest);
    }
}
