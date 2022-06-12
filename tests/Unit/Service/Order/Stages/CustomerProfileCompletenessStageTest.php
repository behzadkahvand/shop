<?php

namespace App\Tests\Unit\Service\Order\Stages;

use App\Entity\Customer;
use App\Service\Order\CreateOrderPayload;
use App\Service\Order\Stages\CustomerProfileCompletenessStage;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class CustomerProfileCompletenessStageTest
 */
final class CustomerProfileCompletenessStageTest extends MockeryTestCase
{
    public function testGetTagAndPriority()
    {
        self::assertEquals('app.pipeline_stage.order_processing', CustomerProfileCompletenessStage::getTag());
        self::assertEquals(105, CustomerProfileCompletenessStage::getPriority());
    }

    public function testItThrowExceptionIfProfileIsNotCompleted()
    {
        $customer = \Mockery::mock(Customer::class);

        $validator = \Mockery::mock(ValidatorInterface::class);
        $validator->shouldReceive('validate')
                  ->once()
                  ->with($customer, null, ['order.store'])
                  ->andReturnUsing(function () {
                      $violations = \Mockery::mock(ConstraintViolationListInterface::class);
                      $violations->shouldReceive('count')->once()->withNoArgs()->andReturn(1);

                      return $violations;
                  });

        $stage = new CustomerProfileCompletenessStage($validator);

        $payload  = \Mockery::mock(CreateOrderPayload::class);
        $payload->shouldReceive('getCart->getCustomer')
                ->once()
                ->withNoArgs()
                ->andReturn($customer);

        try {
            $stage($payload);
        } catch (ValidationFailedException $e) {
            $violations = $e->getViolations();

            self::assertInstanceOf(ConstraintViolationList::class, $violations);

            foreach ($violations as $violation) {
                self::assertInstanceOf(ConstraintViolation::class, $violation);
                self::assertEquals('Customer profile is not completed', $violation->getMessage());
                self::assertEquals('customerProfile', $violation->getPropertyPath());
            }
        }
    }

    public function testItDoesNotThrowExceptionCustomerIfProfileIsComplete()
    {
        $customer = \Mockery::mock(Customer::class);

        $validator = \Mockery::mock(ValidatorInterface::class);
        $validator->shouldReceive('validate')
                  ->once()
                  ->with($customer, null, ['order.store'])
                  ->andReturnUsing(function () {
                      $violations = \Mockery::mock(ConstraintViolationListInterface::class);
                      $violations->shouldReceive('count')->once()->withNoArgs()->andReturn(0);

                      return $violations;
                  });

        $stage = new CustomerProfileCompletenessStage($validator);

        $payload  = \Mockery::mock(CreateOrderPayload::class);
        $payload->shouldReceive('getCart->getCustomer')
                ->once()
                ->withNoArgs()
                ->andReturn($customer);

        self::assertSame($payload, $stage($payload));
    }
}
