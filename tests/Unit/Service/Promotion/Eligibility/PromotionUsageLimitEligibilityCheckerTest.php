<?php

namespace App\Tests\Unit\Service\Promotion\Eligibility;

use App\Entity\Promotion;
use App\Repository\OrderRepository;
use App\Service\Promotion\Eligibility\PromotionUsageLimitEligibilityChecker;
use App\Service\Promotion\PromotionSubjectInterface;
use Generator;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class PromotionUsageLimitEligibilityCheckerTest extends MockeryTestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        Mockery::close();
    }

    public function testIsEligibleWhenNotLimited(): void
    {
        $orderRepository = Mockery::mock(OrderRepository::class);
        $subject         = Mockery::mock(PromotionSubjectInterface::class);

        $promotion = Mockery::mock(Promotion::class);
        $promotion->shouldReceive('getUsageLimit')->once()->withNoArgs()->andReturnNull();

        $checker = new PromotionUsageLimitEligibilityChecker($orderRepository);

        self::assertTrue($checker->isEligible($subject, $promotion));
    }

    public function data(): Generator
    {
        yield [10, 9, true];
        yield [10, 10, false];
        yield [10, 11, false];
    }

    /**
     * @dataProvider data
     */
    public function testIsEligible($limit, $usage, $result): void
    {
        $promotion = Mockery::mock(Promotion::class);
        $promotion->shouldReceive('getUsageLimit')->once()->withNoArgs()->andReturn($limit);

        $orderRepository = Mockery::mock(OrderRepository::class);
        $orderRepository->shouldReceive('countByPromotion')->once()->with($promotion)->andReturn($usage);

        $subject = Mockery::mock(PromotionSubjectInterface::class);

        $checker = new PromotionUsageLimitEligibilityChecker($orderRepository);

        self::assertEquals($result, $checker->isEligible($subject, $promotion));
    }
}
