<?php

namespace App\Tests\Unit\Service\Order\Stages;

use App\Entity\Cart;
use App\Service\Order\CreateOrderPayload;
use App\Service\Order\Stages\PreventCartWithoutCartItemStage;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class PreventCartWithoutCartItemStageTest
 */
final class PreventCartWithoutCartItemStageTest extends MockeryTestCase
{
    public function testGetTagAndPriority()
    {
        self::assertEquals('app.pipeline_stage.order_processing', PreventCartWithoutCartItemStage::getTag());
        self::assertEquals(104, PreventCartWithoutCartItemStage::getPriority());
    }

    public function testItThrowExceptionIfCartIsEmpty()
    {
        $cart = \Mockery::mock(Cart::class);

        $validator = \Mockery::mock(ValidatorInterface::class);
        $validator->shouldReceive('validate')
                  ->once()
                  ->with($cart, null, ['order.store'])
                  ->andReturnUsing(function () {
                      $violations = \Mockery::mock(ConstraintViolationListInterface::class);
                      $violations->shouldReceive('count')->once()->withNoArgs()->andReturn(1);

                      return $violations;
                  });

        $stage = new PreventCartWithoutCartItemStage($validator);

        $payload  = \Mockery::mock(CreateOrderPayload::class);
        $payload->shouldReceive('getCart')
                ->once()
                ->withNoArgs()
                ->andReturn($cart);

        $this->expectException(ValidationFailedException::class);

        $stage($payload);
    }

    public function testItDoesNotThrowExceptionCustomerIfProfileIsComplete()
    {
        $cart = \Mockery::mock(Cart::class);

        $validator = \Mockery::mock(ValidatorInterface::class);
        $validator->shouldReceive('validate')
                  ->once()
                  ->with($cart, null, ['order.store'])
                  ->andReturnUsing(function () {
                      $violations = \Mockery::mock(ConstraintViolationListInterface::class);
                      $violations->shouldReceive('count')->once()->withNoArgs()->andReturn(0);

                      return $violations;
                  });

        $stage = new PreventCartWithoutCartItemStage($validator);

        $payload  = \Mockery::mock(CreateOrderPayload::class);
        $payload->shouldReceive('getCart')
                ->once()
                ->withNoArgs()
                ->andReturn($cart);

        self::assertSame($payload, $stage($payload));
    }
}
