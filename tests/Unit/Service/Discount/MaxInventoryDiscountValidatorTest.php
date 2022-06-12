<?php

namespace App\Tests\Unit\Service\Discount;

use App\Dictionary\InventoryDiscount;
use App\Entity\Configuration;
use App\Exceptions\Discount\InventoryDiscountRuleViolationException;
use App\Service\Configuration\ConfigurationServiceInterface;
use App\Service\Discount\MaxInventoryDiscountValidator;
use App\Tests\Unit\BaseUnitTestCase;
use App\Tests\Unit\TestDoubles\Spies\SpyMaxInventoryDiscountValidator;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class MaxInventoryDiscountValidatorTest extends BaseUnitTestCase
{
    protected const DEFAULT_DISCOUNT_LIMIT = 80;

    private LegacyMockInterface|MockInterface|ConfigurationServiceInterface|null $config;
    private TranslatorInterface|LegacyMockInterface|MockInterface|null $translator;

    public function setUp(): void
    {
        parent::setUp();

        $this->config = Mockery::mock(ConfigurationServiceInterface::class);
        $this->translator = Mockery::mock(TranslatorInterface::class);
    }

    public function testShouldReadLimitFromConfigIfAvailable(): void
    {
        $limit = 60;
        $configuration = Mockery::mock(Configuration::class);
        $configuration->shouldReceive('getValue')->once()->withNoArgs()->andReturn($limit);
        $this->config->shouldReceive('findByCode')->once()->with(InventoryDiscount::MAX_LIMIT)->andReturn($configuration);

        $sut = new SpyMaxInventoryDiscountValidator($this->config, $this->translator, self::DEFAULT_DISCOUNT_LIMIT);

        self::assertEquals($limit, $sut->limit);
    }

    public function testShouldReadDefaultLimitIfConfigIsNotAvailable(): void
    {
        $this->config->shouldReceive('findByCode')->once()->with(InventoryDiscount::MAX_LIMIT)->andReturnNull();

        $sut = new SpyMaxInventoryDiscountValidator($this->config, $this->translator, self::DEFAULT_DISCOUNT_LIMIT);

        self::assertEquals(self::DEFAULT_DISCOUNT_LIMIT, $sut->limit);
    }

    public function testShouldThrowExceptionIfDiscountIsGreaterThanLimit(): void
    {
        $msg = 'dummy message';
        $this->config->shouldReceive('findByCode')->once()->with(InventoryDiscount::MAX_LIMIT)->andReturnNull();
        $this->translator
            ->shouldReceive('trans')
            ->once()
            ->with('max_inventory_discount_exceeded', ['limit' => self::DEFAULT_DISCOUNT_LIMIT], 'exceptions')
            ->andReturn($msg);
        $sut = new MaxInventoryDiscountValidator($this->config, $this->translator, self::DEFAULT_DISCOUNT_LIMIT);

        $this->expectException(InventoryDiscountRuleViolationException::class);
        $this->expectErrorMessage($msg);

        $sut->validate(self::DEFAULT_DISCOUNT_LIMIT + 5);
    }

    public function testShouldNotThrowExceptionIfDiscountIsEqualToLimit(): void
    {
        $this->config->shouldReceive('findByCode')->once()->with(InventoryDiscount::MAX_LIMIT)->andReturnNull();
        $sut = new MaxInventoryDiscountValidator($this->config, $this->translator, self::DEFAULT_DISCOUNT_LIMIT);

        $sut->validate(self::DEFAULT_DISCOUNT_LIMIT);
    }

    public function testShouldNotThrowExceptionIfDiscountIsSmallerThanLimit(): void
    {
        $this->config->shouldReceive('findByCode')->once()->with(InventoryDiscount::MAX_LIMIT)->andReturnNull();
        $sut = new MaxInventoryDiscountValidator($this->config, $this->translator, self::DEFAULT_DISCOUNT_LIMIT);

        $sut->validate(self::DEFAULT_DISCOUNT_LIMIT - 5);
    }

    public function testShouldOnlyReadConfigOnceWhenBeingCalledMultipleTimes(): void
    {
        $limit = 60;
        $configuration = Mockery::mock(Configuration::class);
        $configuration->shouldReceive('getValue')->once()->withNoArgs()->andReturn($limit);
        $this->config->shouldReceive('findByCode')->once()->with(InventoryDiscount::MAX_LIMIT)->andReturn($configuration);
        $sut = new MaxInventoryDiscountValidator($this->config, $this->translator, self::DEFAULT_DISCOUNT_LIMIT);

        $sut->validate($limit - 5);
        $sut->validate($limit - 10);
        $sut->validate($limit - 10);
    }
}
