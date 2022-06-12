<?php

namespace App\Tests\Unit\Service\ORM\CustomFilters\Order\Customer;

use App\Dictionary\CustomerOrderStatus;
use App\Service\Order\OrderStatus\Exceptions\OrderStatusException;
use App\Service\ORM\CustomFilters\Order\Customer\CustomerOrderStatusCustomFilter;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\HttpFoundation\Request;

class CustomerOrderStatusCustomFilterTest extends MockeryTestCase
{
    protected CustomerOrderStatusCustomFilter $orderStatusFilter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderStatusFilter = new CustomerOrderStatusCustomFilter();
    }

    protected function tearDown(): void
    {
        unset($this->orderStatusFilter);
    }

    public function testItDoNothingWhenStatusFilterIsNotSet()
    {
        $request = new Request();

        $this->orderStatusFilter->apply($request);

        self::assertEquals([], $request->query->all());
    }

    public function testItThrowsExceptionWhenOrderStatusIsInvalid()
    {
        $request = new Request(['filter' => ['status' => 'INVALID']]);

        $this->expectException(OrderStatusException::class);
        $this->expectExceptionCode(422);
        $this->expectExceptionMessage('Order status is invalid!');

        $this->orderStatusFilter->apply($request);
    }

    public function testItCanSetOrderStatusCustomFilter()
    {
        $orderStatus = 'CURRENT';

        $request = new Request(['filter' => ['status' => ['like' => $orderStatus]]]);

        $this->orderStatusFilter->apply($request);

        $orderStatuses = collect(CustomerOrderStatus::toArray())->mapToGroups(
            fn($value, $key) => [$value => $key]
        )->toArray();

        self::assertEquals([
            'filter' => [
                'status' => [
                    'in' => implode(',', $orderStatuses[$orderStatus])
                ]
            ]
        ], $request->query->all());
    }
}
