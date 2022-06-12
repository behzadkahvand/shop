<?php

namespace App\Tests\Unit\Service\Promotion\Eligibility;

use App\Entity\Promotion;
use App\Service\Promotion\Eligibility\WalletEligibilityChecker;
use App\Service\Promotion\PromotionSubjectInterface;
use App\Tests\Unit\BaseUnitTestCase;
use Mockery;

class WalletEligibilityCheckerTest extends BaseUnitTestCase
{
    public function testShouldReturnFalseIfCustomerHasWalletBalance(): void
    {
        $promotion = Mockery::mock(Promotion::class);
        $subject = Mockery::mock(PromotionSubjectInterface::class);

        $subject->expects('getCustomer->getWalletBalance')->withNoArgs()->andReturn(1000);

        $sut = new WalletEligibilityChecker();
        self::assertFalse($sut->isEligible($subject, $promotion));
    }

    public function testShouldReturnTrueIfCustomerDoesNotHaveWalletBalance(): void
    {
        $promotion = Mockery::mock(Promotion::class);
        $subject = Mockery::mock(PromotionSubjectInterface::class);

        $subject->expects('getCustomer->getWalletBalance')->withNoArgs()->andReturn(0);

        $sut = new WalletEligibilityChecker();
        self::assertTrue($sut->isEligible($subject, $promotion));
    }

    public function testShouldSetErrorMessageOnContextWhenNotEligible(): void
    {
        $promotion = Mockery::mock(Promotion::class);
        $subject = Mockery::mock(PromotionSubjectInterface::class);

        $subject->expects('getCustomer->getWalletBalance')->withNoArgs()->andReturn(1000);

        $context = [];
        $sut = new WalletEligibilityChecker();
        self::assertFalse($sut->isEligible($subject, $promotion, $context));
        self::assertNotEmpty($context);
        self::assertArrayHasKey('error_messages', $context);
        self::assertEquals(
            ['امکان استفاده از کد تخفیف وقتی حساب کاربری دارای موجودی می‌باشد وجود ندارد.'],
            $context['error_messages']
        );
    }
}
