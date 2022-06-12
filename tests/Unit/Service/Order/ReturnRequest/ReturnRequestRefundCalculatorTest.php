<?php

namespace App\Tests\Unit\Service\Order\ReturnRequest;

use App\Entity\OrderItem;
use App\Entity\ReturnRequest;
use App\Entity\ReturnRequestItem;
use App\Service\Order\ReturnRequest\Refund\ReturnRequestRefundCalculator;
use App\Tests\Unit\BaseUnitTestCase;
use Mockery;
use Mockery\Mock;

class ReturnRequestRefundCalculatorTest extends BaseUnitTestCase
{
    private ReturnRequest|null $returnRequest;
    private ReturnRequestItem|null $item_1;
    private ReturnRequestItem|null $item_2;
    private Mock|OrderItem|null $orderItem_1;
    private Mock|OrderItem|null $orderItem_2;

    protected function setUp(): void
    {
        $this->returnRequest = new ReturnRequest();
        $this->item_1 = new ReturnRequestItem();
        $this->item_2 = new ReturnRequestItem();
        $this->orderItem_1 = Mockery::mock(OrderItem::class)->makePartial();
        $this->orderItem_2 = Mockery::mock(OrderItem::class)->makePartial();
        $this->returnRequest->addItem($this->item_1);
        $this->returnRequest->addItem($this->item_2);
        $this->item_1->setOrderItem($this->orderItem_1);
        $this->item_2->setOrderItem($this->orderItem_2);
    }

    public function testShouldCalculateAndSetRefundAmountOnRequestItemsCorrectly(): void
    {
        $orderItem_1_grandTotal = 5000;
        $orderItem_2_grandTotal = 7000;
        $orderItem_1_discount = 1000;
        $orderItem_2_discount = 2000;
        $orderItem_1_quantity = 2;
        $orderItem_2_quantity = 1;
        $returnRequestItem_1_quantity = 1;
        $returnRequestItem_2_quantity = 1;
        $this->orderItem_1->setQuantity($orderItem_1_quantity);
        $this->orderItem_2->setQuantity($orderItem_2_quantity);
        $this->item_1->setQuantity($returnRequestItem_1_quantity);
        $this->item_2->setQuantity($returnRequestItem_2_quantity);

        $this->orderItem_1->shouldReceive('getDiscountAmount')->once()->withNoArgs()->andReturn($orderItem_1_discount);
        $this->orderItem_2->shouldReceive('getDiscountAmount')->once()->withNoArgs()->andReturn($orderItem_2_discount);
        $this->orderItem_1->setGrandTotal($orderItem_1_grandTotal);
        $this->orderItem_2->setGrandTotal($orderItem_2_grandTotal);

        $orderItem_1_discountPerItem = $orderItem_1_discount / $orderItem_1_quantity;
        $orderItem_2_discountPerItem = $orderItem_2_discount / $orderItem_2_quantity;
        $orderItem_1_payablePerItem = $orderItem_1_grandTotal / $orderItem_1_quantity - $orderItem_1_discountPerItem;
        $orderItem_2_payablePerItem = $orderItem_2_grandTotal / $orderItem_2_quantity - $orderItem_2_discountPerItem;

        $sut = new ReturnRequestRefundCalculator();

        $sut->calculate($this->returnRequest);

        $item_1_refundAmount = $returnRequestItem_1_quantity * $orderItem_1_payablePerItem;
        $item_2_refundAmount = $returnRequestItem_2_quantity * $orderItem_2_payablePerItem;

        self::assertEquals($item_1_refundAmount, $this->item_1->getRefundAmount());
        self::assertEquals($item_2_refundAmount, $this->item_2->getRefundAmount());
    }
}
