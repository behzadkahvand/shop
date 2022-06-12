<?php

namespace App\Tests\Unit\Service\Promotion;

use App\Entity\Order;
use App\Service\Promotion\PromotionSubjectInterface;
use App\Service\Promotion\SubjectLockChecker;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class SubjectLockCheckerTest extends MockeryTestCase
{
    private SubjectLockChecker $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new SubjectLockChecker();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->sut);

        Mockery::close();
    }

    public function testIsLockReturnTrueForLockedLockableSubject(): void
    {
        $subject = Mockery::mock(Order::class);
        $subject->shouldReceive('isLockedPromotion')->withNoArgs()->andReturn(true);

        self::assertTrue($this->sut->isLocked($subject));
    }

    public function testIsLockReturnFalseForNonLockedLockableSubject(): void
    {
        $subject = Mockery::mock(Order::class);
        $subject->shouldReceive('isLockedPromotion')->withNoArgs()->andReturn(false);

        self::assertFalse($this->sut->isLocked($subject));
    }

    public function testIsLockReturnFalseForNonOrderSubject(): void
    {
        $subject = Mockery::mock(PromotionSubjectInterface::class);

        self::assertFalse($this->sut->isLocked($subject));
    }
}
