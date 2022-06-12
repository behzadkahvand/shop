<?php

namespace App\Tests\Unit\Service\ORM\CustomFilters\Order\Seller;

use App\Dictionary\SellerOrderItemStatus;
use App\Dictionary\SellerOrderStatus;
use App\Service\ORM\CustomFilters\Order\Seller\SellerOrderStatusCustomFilter;
use App\Service\Seller\SellerOrderItem\Exceptions\InvalidSellerOrderStatusException;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\HttpFoundation\Request;

class SellerOrderStatusCustomFilterTest extends MockeryTestCase
{
    protected SellerOrderStatusCustomFilter $orderStatusFilter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderStatusFilter = new SellerOrderStatusCustomFilter();
    }

    protected function tearDown(): void
    {
        unset($this->orderStatusFilter);
    }

    public function testItDoNothingWhenOrderStatusFilterDoesNotSet()
    {
        $request = new Request(['filter' => ['status' => SellerOrderItemStatus::SENT_BY_SELLER]]);

        $this->orderStatusFilter->apply($request);

        self::assertEquals(['filter' => ['status' => SellerOrderItemStatus::SENT_BY_SELLER]], $request->query->all());
    }

    public function testItThrowsExceptionWhenOrderStatusIsInvalid()
    {
        $request = new Request(['filter' => ['orderStatus' => 'INVALID']]);

        $this->expectException(InvalidSellerOrderStatusException::class);
        $this->expectExceptionCode(422);
        $this->expectExceptionMessage('The seller order status is invalid!');

        $this->orderStatusFilter->apply($request);
    }

    public function testItCanSetOrderStatusCustomFilter()
    {
        $orderStatus = SellerOrderStatus::WAITING_FOR_PAY;

        $request = new Request(['filter' => ['orderStatus' => $orderStatus]]);

        $this->orderStatusFilter->apply($request);

        $sellerOrderStatuses = collect(SellerOrderStatus::toArray())->mapToGroups(fn($value, $key) => [$value => $key])
                                                                    ->toArray();

        self::assertEquals([
            'filter' => [
                'orderItem.order.status' => [
                    'in' => implode(',', $sellerOrderStatuses[$orderStatus])
                ]
            ]
        ], $request->query->all());
    }

    public function testItThrowExceptionIfStatusIsNotEqualityAndOperatorIsNotIn()
    {
        $orderStatus = SellerOrderStatus::WAITING_FOR_PAY;

        $request = new Request(['filter' => ['orderStatus' => ['gt' => $orderStatus]]]);

        $this->expectException(InvalidSellerOrderStatusException::class);

        $this->orderStatusFilter->apply($request);
    }

    public function testItApplyInOperatorFilter()
    {
        $request = new Request(['filter' => ['orderStatus' => ['in' => 'RESERVED,CONFIRMED']]]);

        $this->orderStatusFilter->apply($request);

        self::assertEquals([
            'filter' => [
                'orderItem.order.status' => [
                    'in' => 'NEW,WAITING,WAIT_CUSTOMER,CALL_FAILED,WAITING_FOR_PAY,CONFIRMED'
                ]
            ]
        ], $request->query->all());
    }
}
