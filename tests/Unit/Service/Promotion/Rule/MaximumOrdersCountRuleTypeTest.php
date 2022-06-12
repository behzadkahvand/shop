<?php

namespace App\Tests\Unit\Service\Promotion\Rule;

use App\Entity\Customer;
use App\Repository\OrderRepository;
use App\Service\Promotion\ContextOperationManager;
use App\Service\Promotion\PromotionSubjectInterface;
use App\Service\Promotion\Rule\MaximumOrdersCountRuleType;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Symfony\Component\Form\AbstractType;

class MaximumOrdersCountRuleTypeTest extends MockeryTestCase
{
    private MaximumOrdersCountRuleType $sut;

    private ContextOperationManager|LegacyMockInterface|MockInterface|null $contextOperationManager;

    private LegacyMockInterface|MockInterface|OrderRepository|null $orderRepository;

    private LegacyMockInterface|PromotionSubjectInterface|MockInterface|null $subject;

    private LegacyMockInterface|MockInterface|Customer|null $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->contextOperationManager = Mockery::mock(ContextOperationManager::class);
        $this->orderRepository = Mockery::mock(OrderRepository::class);
        $this->subject = Mockery::mock(PromotionSubjectInterface::class);
        $this->customer = Mockery::mock(Customer::class);

        $this->sut = new MaximumOrdersCountRuleType($this->orderRepository, $this->contextOperationManager);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->contextOperationManager = null;
        $this->orderRepository = null;
        $this->subject = null;
        $this->customer = null;

        unset($this->sut);

        Mockery::close();
    }

    public function testShouldReturnFalseIfOrdersCountConfigurationIsNotSetOrIsInvalid(): void
    {
        self::assertFalse($this->sut->isValid($this->subject, []));
        self::assertFalse($this->sut->isValid($this->subject, ['orders_count' => 'one']));
    }

    public function testShouldReturnFalseIfSubjectDoesNotHaveCustomer(): void
    {
        $this->subject->shouldReceive('getCustomer')->once()->withNoArgs()->andReturnNull();

        self::assertFalse($this->sut->isValid($this->subject, ['orders_count' => 1]));
    }

    public function testShouldReturnFalseIfCustomerDoesNotHaveId(): void
    {
        $this->customer->shouldReceive('getId')->once()->withNoArgs()->andReturnNull();
        $this->subject->shouldReceive('getCustomer')->once()->withNoArgs()->andReturn($this->customer);

        self::assertFalse($this->sut->isValid($this->subject, ['orders_count' => 1]));
    }

    public function testShouldReturnFalseIfCustomerOrdersCountExceedsLimit(): void
    {
        $customerOrdersCount = 2;
        $ordersCountLimit = 1;

        $this->customer->shouldReceive('getId')->once()->withNoArgs()->andReturn(1);
        $this->subject->shouldReceive('getCustomer')->once()->withNoArgs()->andReturn($this->customer);
        $this->subject->shouldReceive('getId')->once()->withNoArgs()->andReturnNull();
        $this->orderRepository
            ->shouldReceive('countByCustomer')
            ->once()
            ->with($this->customer, [])
            ->andReturn($customerOrdersCount);

        self::assertFalse($this->sut->isValid($this->subject, ['orders_count' => $ordersCountLimit]));
    }

    public function testShouldAddErrorMsgToOperationManagerIfCustomerOrdersCountExceedsLimitAndLimitIsSetToFirstOrder(): void
    {
        $customerOrdersCount = 2;
        $ordersCountLimit = 0;

        $this->customer->shouldReceive('getId')->once()->withNoArgs()->andReturn(1);
        $this->subject->shouldReceive('getCustomer')->once()->withNoArgs()->andReturn($this->customer);
        $this->subject->shouldReceive('getId')->once()->withNoArgs()->andReturnNull();
        $this->orderRepository
            ->shouldReceive('countByCustomer')
            ->once()
            ->with($this->customer, [])
            ->andReturn($customerOrdersCount);

        $this->contextOperationManager
            ->shouldReceive('addErrorMessage')
            ->once()
            ->with([], 'این کد تخفیف تنها برای خرید اول قابل استفاده است. ')->andReturnNull();

        self::assertFalse($this->sut->isValid($this->subject, ['orders_count' => $ordersCountLimit]));
    }

    public function testShouldReturnTrueIfCustomerOrdersCountIsSmallerThanLimit(): void
    {
        $customerOrdersCount = 2;
        $ordersCountLimit = 3;

        $this->customer->shouldReceive('getId')->once()->withNoArgs()->andReturn(1);
        $this->subject->shouldReceive('getCustomer')->once()->withNoArgs()->andReturn($this->customer);
        $this->subject->shouldReceive('getId')->once()->withNoArgs()->andReturnNull();
        $this->orderRepository
            ->shouldReceive('countByCustomer')
            ->once()
            ->with($this->customer, [])
            ->andReturn($customerOrdersCount);

        self::assertTrue($this->sut->isValid($this->subject, ['orders_count' => $ordersCountLimit]));
    }

    public function testShouldReturnTrueIfCustomerOrdersCountIsEqualToLimit(): void
    {
        $customerOrdersCount = 2;
        $ordersCountLimit = 2;

        $this->customer->shouldReceive('getId')->once()->withNoArgs()->andReturn(1);
        $this->subject->shouldReceive('getCustomer')->once()->withNoArgs()->andReturn($this->customer);
        $this->subject->shouldReceive('getId')->once()->withNoArgs()->andReturnNull();
        $this->orderRepository
            ->shouldReceive('countByCustomer')
            ->once()
            ->with($this->customer, [])
            ->andReturn($customerOrdersCount);

        self::assertTrue($this->sut->isValid($this->subject, ['orders_count' => $ordersCountLimit]));
    }

    public function testGetName(): void
    {
        self::assertIsString(MaximumOrdersCountRuleType::getName());
    }

    public function testConfigurationFormType(): void
    {
        $ruleType = new MaximumOrdersCountRuleType($this->orderRepository, $this->contextOperationManager);
        self::assertTrue(is_subclass_of($ruleType->getConfigurationFormType(), AbstractType::class));
    }
}
