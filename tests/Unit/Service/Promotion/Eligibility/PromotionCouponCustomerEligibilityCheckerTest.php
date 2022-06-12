<?php

namespace App\Tests\Unit\Service\Promotion\Eligibility;

use App\Entity\Customer;
use App\Entity\PromotionCoupon;
use App\Service\Promotion\ContextOperationManager;
use App\Service\Promotion\Eligibility\PromotionCouponCustomerEligibilityChecker;
use App\Service\Promotion\PromotionSubjectInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;

class PromotionCouponCustomerEligibilityCheckerTest extends MockeryTestCase
{
    private ContextOperationManager|LegacyMockInterface|MockInterface|null $contextOperationManager;

    private LegacyMockInterface|MockInterface|PromotionCoupon|null $promotionCoupon;

    private LegacyMockInterface|PromotionSubjectInterface|MockInterface|null $subject;

    private PromotionCouponCustomerEligibilityChecker $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->contextOperationManager = Mockery::mock(ContextOperationManager::class);
        $this->promotionCoupon         = Mockery::mock(PromotionCoupon::class);
        $this->subject                 = Mockery::mock(PromotionSubjectInterface::class);

        $this->sut = new PromotionCouponCustomerEligibilityChecker($this->contextOperationManager);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->sut);

        $this->contextOperationManager = null;
        $this->promotionCoupon         = null;
        $this->subject                 = null;

        Mockery::close();
    }

    public function testIsEligibleWithoutCustomer(): void
    {
        $this->promotionCoupon->shouldReceive('getCustomers')->once()->withNoArgs()->andReturn(new ArrayCollection());

        self::assertTrue($this->sut->isEligible($this->subject, $this->promotionCoupon));
    }

    public function testIsEligibleWithCustomer(): void
    {
        $customer  = Mockery::mock(Customer::class);
        $customer2 = Mockery::mock(Customer::class);

        $this->subject->shouldReceive('getCustomer')->once()->withNoArgs()->andReturn($customer);

        $this->promotionCoupon->shouldReceive('getCustomers')->once()->withNoArgs()->andReturn(new ArrayCollection([$customer, $customer2]));
        $this->promotionCoupon->shouldReceive('containsCustomer')->once()->with($customer)->andReturnTrue();

        self::assertTrue($this->sut->isEligible($this->subject, $this->promotionCoupon));
    }

    public function testIsNotEligibleWithCustomer(): void
    {
        $customer  = Mockery::mock(Customer::class);
        $customer2 = Mockery::mock(Customer::class);

        $this->contextOperationManager
            ->shouldReceive('addErrorMessage')
            ->once()
            ->with([], 'این کد تخفیف برای شما معتبر نمی باشد. ')
            ->andReturnNull();

        $this->subject->shouldReceive('getCustomer')->once()->withNoArgs()->andReturn($customer);

        $this->promotionCoupon->shouldReceive('getCustomers')->once()->withNoArgs()->andReturn(new ArrayCollection([$customer2]));
        $this->promotionCoupon->shouldReceive('containsCustomer')->once()->with($customer)->andReturnFalse();

        self::assertFalse($this->sut->isEligible($this->subject, $this->promotionCoupon));
    }
}
