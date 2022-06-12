<?php

namespace App\Tests\Unit\Service\Promotion\Builders;

use App\Entity\Customer;
use App\Service\Promotion\DTO\PromotionCouponDTO;
use App\Service\Promotion\Builders\PromotionCouponDTOBuilder;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class PromotionCouponDTOBuilderTest extends MockeryTestCase
{
    private PromotionCouponDTOBuilder $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new PromotionCouponDTOBuilder();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->sut);

        Mockery::close();
    }

    public function testShouldBuildRawPromotionCouponDTO(): void
    {
        $actual = $this->sut->build();

        self::assertEquals(new PromotionCouponDTO(), $actual);
    }

    public function testShouldBuildPromotionCouponDTOWithCustomers(): void
    {
        $customers = [
            Mockery::mock(Customer::class),
            Mockery::mock(Customer::class)
        ];

        $actual = $this->sut->withCustomers($customers)->build();

        $expected = new PromotionCouponDTO();
        $expected->addCustomers($customers);
        self::assertEquals($expected, $actual);
    }
}
