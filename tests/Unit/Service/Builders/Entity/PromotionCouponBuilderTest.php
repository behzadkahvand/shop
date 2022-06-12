<?php

namespace App\Tests\Unit\Service\Builders\Entity;

use App\Service\Builders\Entity\PromotionCouponBuilder;
use App\Entity\Promotion;
use App\Entity\PromotionCoupon;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class PromotionCouponBuilderTest extends MockeryTestCase
{
    private PromotionCouponBuilder $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new PromotionCouponBuilder();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->sut);
    }

    public function testShouldBuildRawPromotionCoupon(): void
    {
        $actual = $this->sut->build();

        self::assertEquals(new PromotionCoupon(), $actual);
    }

    public function testShouldBuildRawPromotionCouponWithPromotion(): void
    {
        $promotion = Mockery::mock(Promotion::class);

        $actual = $this->sut->withPromotion($promotion)->build();

        $expected = (new PromotionCoupon())->setPromotion($promotion);
        self::assertEquals($expected, $actual);
        self::assertSame($promotion, $actual->getPromotion());
    }
}
