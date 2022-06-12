<?php

namespace App\Tests\Unit\Service\ORM\CustomFilters\Order\Admin;

use App\Dictionary\OrderBalanceStatus;
use App\Service\ORM\CustomFilters\Order\Admin\OrderBalanceStatusCustomFilter;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class OrderBalanceStatusCustomFilterTest
 */
final class OrderBalanceStatusCustomFilterTest extends MockeryTestCase
{
    protected OrderBalanceStatusCustomFilter $customFilter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customFilter = new OrderBalanceStatusCustomFilter();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->customFilter);
    }

    public function testItReturnIfBalanceStatusFilterIsNotSet(): void
    {
        $request = new Request();
        $this->customFilter->apply($request);

        self::assertEquals([], $request->query->all());
    }

    public function testItApplyBalanceAmountFilterWhenBalanceStatusIsCreditor(): void
    {
        $request = new Request(['filter' => ['balanceStatus' => OrderBalanceStatus::CREDITOR]]);

        $this->customFilter->apply($request);

        self::assertEquals(['filter' => [
            'balanceAmount' => [
                'gt' => 0
            ]
        ]], $request->query->all());
    }

    public function testItApplyBalanceAmountFilterWhenBalanceStatusIsDebtor(): void
    {
        $request = new Request(['filter' => ['balanceStatus' => OrderBalanceStatus::DEBTOR]]);

        $this->customFilter->apply($request);

        self::assertEquals(['filter' => [
            'balanceAmount' => [
                'lt' => 0
            ]
        ]], $request->query->all());
    }

    public function testItApplyBalanceAmountFilterWhenBalanceStatusIsBalance(): void
    {
        $request = new Request(['filter' => ['balanceStatus' => OrderBalanceStatus::BALANCE]]);

        $this->customFilter->apply($request);

        self::assertEquals(['filter' => [
            'balanceAmount' => 0
        ]], $request->query->all());
    }
}
