<?php

namespace App\Tests\Unit\Service\Promotion\Action;

use App\Entity\Promotion;
use App\Entity\PromotionAction;
use App\Service\Promotion\Action\ActionTypeInterface;
use App\Service\Promotion\Action\ActionTypeRegistryInterface;
use App\Service\Promotion\Action\PromotionApplicator;
use App\Service\Promotion\PromotionSubjectInterface;
use Doctrine\Common\Collections\ArrayCollection;
use InvalidArgumentException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;

class PromotionApplicatorTest extends MockeryTestCase
{
    private Promotion|LegacyMockInterface|MockInterface|null $promotion;

    private LegacyMockInterface|PromotionSubjectInterface|MockInterface|null $subject;

    private LegacyMockInterface|ActionTypeRegistryInterface|MockInterface|null $actionTypeRegistry;

    private PromotionApplicator $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->promotion          = Mockery::mock(Promotion::class);
        $this->subject            = Mockery::mock(PromotionSubjectInterface::class);
        $this->actionTypeRegistry = Mockery::mock(ActionTypeRegistryInterface::class);

        $this->sut = new PromotionApplicator($this->actionTypeRegistry);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->sut);

        $this->promotion          = null;
        $this->subject            = null;
        $this->actionTypeRegistry = null;

        Mockery::close();
    }

    public function testExceptionOnPromotionWithInvalidAction(): void
    {
        $action = Mockery::mock(PromotionAction::class);
        $action->shouldReceive('getType')->once()->withNoArgs()->andReturn('non_existing_action_type');

        $this->actionTypeRegistry->shouldReceive('get')->once()->with('non_existing_action_type')->andReturnNull();

        $this->promotion->shouldReceive('getActions')->once()->withNoArgs()->andReturn(new ArrayCollection([
            $action,
        ]));

        self::expectException(InvalidArgumentException::class);

        $context = [];
        $this->sut->apply($this->subject, $this->promotion, $context);
    }

    public function testApplySuccessfully(): void
    {
        $applyingAction        = Mockery::mock(PromotionAction::class);
        $nonApplyingAction     = Mockery::mock(PromotionAction::class);
        $applyingActionType    = Mockery::mock(ActionTypeInterface::class);
        $nonApplyingActionType = Mockery::mock(ActionTypeInterface::class);

        $this->promotion
            ->shouldReceive('getActions')
            ->once()
            ->withNoArgs()
            ->andReturn(new ArrayCollection([$applyingAction, $nonApplyingAction,]));

        $applyingAction->shouldReceive('getType')->once()->withNoArgs()->andReturn('applying_action');
        $nonApplyingAction->shouldReceive('getType')->once()->withNoArgs()->andReturn('non_applying_action');

        $this->actionTypeRegistry->shouldReceive('get')->once()->with('applying_action')->andReturn($applyingActionType);
        $this->actionTypeRegistry->shouldReceive('get')->once()->with('non_applying_action')->andReturn($nonApplyingActionType);

        $applyingActionType->shouldReceive('apply')->with($this->subject, $applyingAction, $this->promotion, [])->andReturnTrue();
        $nonApplyingActionType->shouldReceive('apply')->with($this->subject, $nonApplyingAction, $this->promotion, [])->andReturnFalse();

        $this->subject->shouldReceive('addPromotion')->once()->with($this->promotion)->andReturnSelf();
        $this->subject->shouldReceive('updateTotals')->once()->withNoArgs()->andReturnSelf();

        $context = [];
        $this->sut->apply($this->subject, $this->promotion, $context);
    }

    public function testRevertSuccessfully(): void
    {
        $firstAction      = Mockery::mock(PromotionAction::class);
        $secondAction     = Mockery::mock(PromotionAction::class);
        $firstActionType  = Mockery::mock(ActionTypeInterface::class);
        $secondActionType = Mockery::mock(ActionTypeInterface::class);

        $firstAction->shouldReceive('getType')->once()->withNoArgs()->andReturn('first_action');
        $secondAction->shouldReceive('getType')->once()->withNoArgs()->andReturn('second_action');

        $this->promotion
            ->shouldReceive('getActions')
            ->once()
            ->withNoArgs()
            ->andReturn(new ArrayCollection([$firstAction, $secondAction,]));

        $this->subject->shouldReceive('removePromotion')->once()->with($this->promotion)->andReturnSelf();
        $this->subject->shouldReceive('updateTotals')->once()->withNoArgs()->andReturnSelf();

        $firstActionType->shouldReceive('revert')->with($this->subject, $firstAction, $this->promotion, [])->andReturnTrue();
        $secondActionType->shouldReceive('revert')->with($this->subject, $secondAction, $this->promotion, [])->andReturnTrue();

        $this->actionTypeRegistry->shouldReceive('get')->once()->with('first_action')->andReturn($firstActionType);
        $this->actionTypeRegistry->shouldReceive('get')->once()->with('second_action')->andReturn($secondActionType);

        $context = [];
        $this->sut->revert($this->subject, $this->promotion, $context);
    }
}
