<?php

namespace App\Tests\Unit\Service\Promotion\Action;

use App\Entity\OrderPromotionDiscount;
use App\Entity\Promotion;
use App\Entity\PromotionAction;
use App\Entity\PromotionDiscount;
use App\Service\Promotion\Action\DiscountCreatorInterface;
use App\Service\Promotion\Action\DiscountValidation\DiscountValidatorInterface;
use App\Service\Promotion\Action\FixedDiscountActionType;
use App\Service\Promotion\PromotionSubjectInterface;
use App\Tests\Unit\BaseTypeTestCase;
use Doctrine\Common\Collections\ArrayCollection;
use InvalidArgumentException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Validator\Validation;

class FixedDiscountActionTypeTest extends BaseTypeTestCase
{
    use MockeryPHPUnitIntegration;

    private LegacyMockInterface|DiscountCreatorInterface|MockInterface|null $discountCreator;

    private DiscountValidatorInterface|LegacyMockInterface|MockInterface|null $discountValidator;

    private LegacyMockInterface|PromotionSubjectInterface|MockInterface|null $subject;

    private FixedDiscountActionType $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->discountCreator   = Mockery::mock(DiscountCreatorInterface::class);
        $this->discountValidator = Mockery::mock(DiscountValidatorInterface::class);
        $this->subject           = Mockery::mock(PromotionSubjectInterface::class);

        $this->sut = new FixedDiscountActionType($this->discountCreator, $this->discountValidator);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->sut);

        $this->discountCreator   = null;
        $this->discountValidator = null;
        $this->subject           = null;

        Mockery::close();
    }

    public function testGetName(): void
    {
        self::assertEquals('fixed_discount', FixedDiscountActionType::getName());
    }

    public function testConfigurationFormType(): void
    {
        self::assertTrue(is_subclass_of($this->sut->getConfigurationFormType(), AbstractType::class));

        $formData = [
            'amount' => 1000,
        ];

        $model            = new class {
            public ?int $amount = null;
        };
        $expected         = clone $model;
        $expected->amount = 1000;

        $form = $this->factory->create($this->sut->getConfigurationFormType(), $model);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expected, $model);
    }

    public function testDoNotApplyWithZeroItemSubject(): void
    {
        $this->subject->shouldReceive('getItemsCount')->once()->withNoArgs()->andReturn(0);

        self::assertFalse($this->sut->apply($this->subject, new PromotionAction(), new Promotion()));
    }

    public function testThrowExceptionWhenConfigurationDoesNotContainAmount(): void
    {
        $this->subject->shouldReceive('getItemsCount')->once()->withNoArgs()->andReturn(1);

        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('Expected the key "amount" to exist.');
        $this->sut->apply($this->subject, new PromotionAction(), new Promotion());
    }

    public function testThrowExceptionWhenAmountIsNotInteger(): void
    {
        $this->subject->shouldReceive('getItemsCount')->once()->withNoArgs()->andReturn(1);

        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('Expected an integer. Got: string');
        $this->sut->apply($this->subject, (new PromotionAction())->setConfiguration(['amount' => 'one']), new Promotion());
    }

    public function testDoNotApplyWithZeroAmountSubject(): void
    {
        $this->subject->shouldReceive('getItemsCount')->once()->withNoArgs()->andReturn(1);
        $this->subject->shouldReceive('getPromotionSubjectTotal')->once()->withNoArgs()->andReturn(0);

        self::assertFalse($this->sut->apply($this->subject, (new PromotionAction())->setConfiguration(['amount' => 1000]), new Promotion()));
    }

    public function testDoNotApplyWithZeroAmountConfiguration(): void
    {
        $this->subject->shouldReceive('getItemsCount')->once()->withNoArgs()->andReturn(1);
        $this->subject->shouldReceive('getPromotionSubjectTotal')->once()->withNoArgs()->andReturn(1000);

        self::assertFalse($this->sut->apply($this->subject, (new PromotionAction())->setConfiguration(['amount' => 0]), new Promotion()));
    }

    public function testApplySuccessfully(): void
    {
        $this->subject->shouldReceive('getItemsCount')->once()->withNoArgs()->andReturn(1);
        $this->subject->shouldReceive('getPromotionSubjectTotal')->once()->withNoArgs()->andReturn(1000);

        $action = (new PromotionAction())->setConfiguration(['amount' => 500]);

        $this->discountCreator->shouldReceive('create')->once()->with($action, $this->subject, [])->andReturn([new OrderPromotionDiscount()]);

        self::assertTrue($this->sut->apply($this->subject, $action, new Promotion()));
    }

    public function testRevertSuccessfully(): void
    {
        $actionOne = Mockery::mock(PromotionAction::class);
        $actionOne->shouldReceive('getId')->once()->withNoArgs()->andReturn(1);

        $actionTwo = Mockery::mock(PromotionAction::class);
        $actionTwo->shouldReceive('getId')->times(3)->withNoArgs()->andReturn(2);

        $promotionDiscountOne = Mockery::mock(PromotionDiscount::class);
        $promotionDiscountOne->shouldReceive('getAction')->once()->withNoArgs()->andReturn($actionOne);

        $promotionDiscountTwo = Mockery::mock(PromotionDiscount::class);
        $promotionDiscountTwo->shouldReceive('getAction')->once()->withNoArgs()->andReturn($actionTwo);

        $this->discountValidator->shouldReceive('shouldRevert')->once()->with($promotionDiscountTwo)->andReturn(true);

        $this->subject->shouldReceive('getDiscounts')->once()->withNoArgs()->andReturn(new ArrayCollection([
            $promotionDiscountOne,
            $promotionDiscountTwo
        ]));
        $this->subject->expects('removeDiscount')->once()->with($promotionDiscountTwo);

        $this->sut->revert($this->subject, $actionTwo, new Promotion());
    }

    protected function getExtensions()
    {
        $validator = Validation::createValidator();

        return [
            new ValidatorExtension($validator),
        ];
    }
}
