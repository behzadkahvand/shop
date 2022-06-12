<?php

namespace App\Tests\Unit\Service\Promotion;

use App\Entity\Promotion;
use App\Entity\PromotionCoupon;
use App\Service\Promotion\Action\PromotionApplicatorInterface;
use App\Service\Promotion\Eligibility\PromotionEligibilityCheckerInterface;
use App\Service\Promotion\PromotionProcessor;
use App\Service\Promotion\PromotionProviderInterface;
use App\Service\Promotion\PromotionSubjectInterface;
use App\Service\Promotion\SubjectLockChecker;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\MockObject\MockObject;

class PromotionProcessorTest extends MockeryTestCase
{
    private MockObject|PromotionSubjectInterface|null $subject;

    private PromotionApplicatorInterface|MockObject|null $promotionApplicator;

    private MockObject|PromotionProviderInterface|null $promotionProvider;

    private MockObject|PromotionEligibilityCheckerInterface|null $promotionEligibilityChecker;

    private MockObject|SubjectLockChecker|null $subjectLockChecker;

    private PromotionProcessor $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject                     = Mockery::mock(PromotionSubjectInterface::class);
        $this->promotionApplicator         = Mockery::mock(PromotionApplicatorInterface::class);
        $this->promotionProvider           = Mockery::mock(PromotionProviderInterface::class);
        $this->promotionEligibilityChecker = Mockery::mock(PromotionEligibilityCheckerInterface::class);
        $this->subjectLockChecker          = Mockery::mock(SubjectLockChecker::class);

        $this->sut = new PromotionProcessor(
            $this->promotionApplicator,
            $this->promotionEligibilityChecker,
            $this->promotionProvider,
            $this->subjectLockChecker
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->sut);

        $this->subject                     = null;
        $this->promotionApplicator         = null;
        $this->promotionProvider           = null;
        $this->promotionEligibilityChecker = null;
        $this->subjectLockChecker          = null;

        Mockery::close();
    }

    public function testRevertExistingPromotions(): void
    {
        $this->subject
            ->shouldReceive('getPromotions')
            ->twice()
            ->andReturn(
                new ArrayCollection([new Promotion(), new Promotion()])
            );

        $this->promotionApplicator
            ->shouldReceive('revert')
            ->twice();

        $this->promotionProvider
            ->shouldReceive('getPromotions')
            ->once()
            ->with($this->subject)
            ->andReturn([]);

        $promotionCoupon = Mockery::mock(PromotionCoupon::class);
        $promotion       = Mockery::mock(Promotion::class);
        $promotionCoupon->shouldReceive('getPromotion')->once()->withNoArgs()->andReturn($promotion);

        $this->subject
            ->shouldReceive('getPromotionCoupon')
            ->withNoArgs()
            ->twice()
            ->andReturn($promotionCoupon);

        $this->subject
            ->shouldReceive('setPromotionCoupon')
            ->once()
            ->with(null)
            ->andReturnSelf();

        $this->sut->process($this->subject, []);
    }

    public function testApplyOnlyForExclusivePromotions(): void
    {
        $nonExclusivePromotion = new Promotion();
        $nonExclusivePromotion->setExclusive(false);
        $exclusivePromotion = new Promotion();
        $exclusivePromotion->setExclusive(true);

        $this->subject
            ->shouldReceive('getPromotions')
            ->twice()
            ->withNoArgs()
            ->andReturn(
                new ArrayCollection()
            );

        $this->promotionApplicator
            ->shouldReceive('apply')
            ->once()
            ->with($this->subject, $exclusivePromotion, [])
            ->andReturnNull();

        $this->promotionProvider
            ->shouldReceive('getPromotions')
            ->once()
            ->with($this->subject)
            ->andReturn([$nonExclusivePromotion, $exclusivePromotion]);

        $this->promotionEligibilityChecker
            ->shouldReceive('isEligible')
            ->once()
            ->with($this->subject, $exclusivePromotion, [])
            ->andReturnTrue();

        $promotionCoupon = Mockery::mock(PromotionCoupon::class);
        $promotion       = Mockery::mock(Promotion::class);
        $promotionCoupon->shouldReceive('getPromotion')->once()->withNoArgs()->andReturn($promotion);

        $this->subject
            ->shouldReceive('getPromotionCoupon')
            ->withNoArgs()
            ->twice()
            ->andReturn($promotionCoupon);

        $this->subject
            ->shouldReceive('setPromotionCoupon')
            ->once()
            ->with(null)
            ->andReturnSelf();

        $this->sut->process($this->subject, []);
    }

    public function testApplyOnlyForMultipleNonExclusive(): void
    {
        $nonExclusivePromotionOne = new Promotion();
        $nonExclusivePromotionOne->setExclusive(false);
        $nonExclusivePromotionTwo = new Promotion();
        $nonExclusivePromotionTwo->setExclusive(false);
        $nonExclusivePromotionThree = new Promotion();
        $nonExclusivePromotionThree->setExclusive(false);

        $this->subject
            ->shouldReceive('getPromotions')
            ->once()
            ->withNoArgs()
            ->andReturn(new ArrayCollection());
        $this->subject
            ->shouldReceive('getPromotionCoupon')
            ->once()
            ->withNoArgs()
            ->andReturnNull();

        $this->promotionProvider
            ->shouldReceive('getPromotions')
            ->once()
            ->with($this->subject)
            ->andReturn([$nonExclusivePromotionOne, $nonExclusivePromotionTwo, $nonExclusivePromotionThree]);

        $this->promotionEligibilityChecker
            ->shouldReceive('isEligible')
            ->once()
            ->with($this->subject, $nonExclusivePromotionOne, [])
            ->andReturnTrue();
        $this->promotionEligibilityChecker
            ->shouldReceive('isEligible')
            ->once()
            ->with($this->subject, $nonExclusivePromotionTwo, [])
            ->andReturnTrue();
        $this->promotionEligibilityChecker
            ->shouldReceive('isEligible')
            ->once()
            ->with($this->subject, $nonExclusivePromotionThree, [])
            ->andReturnFalse();

        $this->promotionApplicator
            ->shouldReceive('apply')
            ->once()
            ->with($this->subject, $nonExclusivePromotionOne, [])
            ->andReturnNull();
        $this->promotionApplicator
            ->shouldReceive('apply')
            ->once()
            ->with($this->subject, $nonExclusivePromotionTwo, [])
            ->andReturnNull();

        $this->sut->process($this->subject, []);
    }
}
