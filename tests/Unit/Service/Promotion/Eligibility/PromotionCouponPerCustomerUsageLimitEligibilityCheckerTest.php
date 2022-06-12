<?php

namespace App\Tests\Unit\Service\Promotion\Eligibility;

use App\Entity\Customer;
use App\Entity\PromotionCoupon;
use App\Repository\OrderRepository;
use App\Service\Promotion\ContextOperationManager;
use App\Service\Promotion\Eligibility\PromotionCouponPerCustomerUsageLimitEligibilityChecker;
use App\Service\Promotion\PromotionSubjectInterface;
use Generator;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;

class PromotionCouponPerCustomerUsageLimitEligibilityCheckerTest extends MockeryTestCase
{
    private ContextOperationManager|LegacyMockInterface|MockInterface|null $contextOperationManager;

    private LegacyMockInterface|PromotionSubjectInterface|MockInterface|null $subject;

    private LegacyMockInterface|MockInterface|PromotionCoupon|null $promotionCoupon;

    private LegacyMockInterface|MockInterface|OrderRepository|null $orderRepository;

    private PromotionCouponPerCustomerUsageLimitEligibilityChecker $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->contextOperationManager = Mockery::mock(ContextOperationManager::class);
        $this->subject                 = Mockery::mock(PromotionSubjectInterface::class);
        $this->promotionCoupon         = Mockery::mock(PromotionCoupon::class);
        $this->orderRepository         = Mockery::mock(OrderRepository::class);

        $this->sut = new PromotionCouponPerCustomerUsageLimitEligibilityChecker(
            $this->orderRepository,
            $this->contextOperationManager
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->sut);

        $this->contextOperationManager = null;
        $this->subject                 = null;
        $this->promotionCoupon         = null;
        $this->orderRepository         = null;

        Mockery::close();
    }

    public function testIsEligibleWhenNotLimited(): void
    {
        $this->promotionCoupon->shouldReceive('getPerCustomerUsageLimit')->once()->withNoArgs()->andReturnNull();

        self::assertTrue($this->sut->isEligible($this->subject, $this->promotionCoupon));
    }

    public function testIsEligibleWhenLimitedWithoutCustomer(): void
    {
        $this->subject->shouldReceive('getCustomer')->once()->withNoArgs()->andReturnNull();
        $this->promotionCoupon->shouldReceive('getPerCustomerUsageLimit')->once()->withNoArgs()->andReturn(10);

        self::assertTrue($this->sut->isEligible($this->subject, $this->promotionCoupon));
    }

    public function data(): Generator
    {
        yield [10, 9, true];
        yield [10, 11, false];
        yield [10, 10, false];
        yield [1, 1, false];
    }

    /**
     * @dataProvider data
     */
    public function testIsEligibleWhenLimitedWithCustomer($limit, $usage, $result): void
    {
        if ($limit === 1) {
            $this->contextOperationManager
                ->shouldReceive('addErrorMessage')
                ->once()
                ->with([], 'این کد تخفیف تنها یکبار قابل استفاده است.')
                ->andReturnNull();
        }

        $customer = Mockery::mock(Customer::class);
        $customer->shouldReceive('getId')->once()->withNoArgs()->andReturn(1);

        $this->subject->shouldReceive('getCustomer')->once()->withNoArgs()->andReturn($customer);

        $this->promotionCoupon->shouldReceive('getPerCustomerUsageLimit')->once()->withNoArgs()->andReturn($limit);

        $this->orderRepository
            ->shouldReceive('countByCustomerAndCoupon')
            ->once()
            ->with($customer, $this->promotionCoupon, null)
            ->andReturn($usage);

        self::assertEquals($result, $this->sut->isEligible($this->subject, $this->promotionCoupon));
    }
}
