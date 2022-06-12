<?php

namespace App\Tests\Unit\Service\PartialShipment\Types;

use App\Entity\ShippingCategory;
use App\Entity\Zone;
use App\Service\Holiday\HolidayServiceInterface;
use App\Service\PartialShipment\Exceptions\PartialShipmentCanNotBeFreezedException;
use App\Service\PartialShipment\Exceptions\PartialShipmentCanNotBeValidatedException;
use App\Service\PartialShipment\Types\AbstractPartialShipment;
use App\Service\PartialShipment\Types\ExpressPartialShipment;
use App\Service\PartialShipment\ValueObject\ExpressPartialDeliveryDate;
use App\Service\PartialShipment\ValueObject\PartialShipmentItem;
use DateTimeImmutable;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

/**
 * Class ExpressPartialShipmentTest
 */
final class ExpressPartialShipmentTest extends MockeryTestCase
{
    public function testGettingSuppliesIn()
    {
        $itemsMaxSuppliesIn = 1;

        $item = Mockery::mock(PartialShipmentItem::class);
        $item->shouldReceive('getSuppliesIn')->once()->withNoArgs()->andReturn($itemsMaxSuppliesIn);

        $shippingCategory = Mockery::mock(ShippingCategory::class);
        $zone = Mockery::mock(Zone::class);
        $partialShipment = new ExpressPartialShipment($shippingCategory, $zone, [$item]);

        self::assertEquals($itemsMaxSuppliesIn, $partialShipment->getItemsMaxSuppliesIn());
    }

    public function testThrowingExceptionIfBaseDeliveryDateIsNotSet()
    {
        $shippingCategory = Mockery::mock(ShippingCategory::class);
        $zone = Mockery::mock(Zone::class);
        $partialShipment = new ExpressPartialShipment($shippingCategory, $zone, []);

        $this->expectException(PartialShipmentCanNotBeValidatedException::class);

        $partialShipment->validate(
            new DateTimeImmutable(),
            Mockery::mock(ValidatorInterface::class),
            Mockery::mock(HolidayServiceInterface::class)
        );
    }

    public function testValidationPass()
    {
        $selectedDatetime = new DateTimeImmutable();
        $violationList    = new ConstraintViolationList([]);

        $shippingCategory = Mockery::mock(ShippingCategory::class);
        $zone = Mockery::mock(Zone::class);

        $partialShipment = new ExpressPartialShipment($shippingCategory, $zone, []);
        $partialShipment->setBaseDeliveryDate($selectedDatetime->modify('-1 day'));

        $validator = Mockery::mock(ValidatorInterface::class);
        $validator->shouldReceive('validate')
                  ->once()
                  ->with($selectedDatetime, Mockery::type(Callback::class))
                  ->andReturn($violationList);

        $result = $partialShipment->validate(
            $selectedDatetime,
            $validator,
            Mockery::mock(HolidayServiceInterface::class)
        );

        self::assertSame($violationList, $result);
        self::assertTrue($partialShipment->isValid());
    }

    public function testValidationFail()
    {
        $selectedDatetime = new DateTimeImmutable();

        $violation = Mockery::mock(ConstraintViolationInterface::class);
        $violation->shouldReceive('getMessage')->once()->withNoArgs()->andReturn('violation message');
        $violation->shouldReceive('getMessageTemplate')->once()->withNoArgs()->andReturn('violation message template');
        $violation->shouldReceive('getParameters')->once()->withNoArgs()->andReturn([]);
        $violation->shouldReceive('getRoot')->once()->withNoArgs()->andReturn('');
        $violation->shouldReceive('getPropertyPath')->once()->withNoArgs()->andReturn('deliveryDate');
        $violation->shouldReceive('getInvalidValue')->once()->withNoArgs()->andReturn($selectedDatetime);
        $violation->shouldReceive('getPlural')->once()->withNoArgs()->andReturnNull();
        $violation->shouldReceive('getCode')->once()->withNoArgs()->andReturnNull();
        $violation->shouldReceive('getConstraint')->once()->withNoArgs()->andReturnNull();

        $violationList = new ConstraintViolationList([$violation]);

        $shippingCategory = Mockery::mock(ShippingCategory::class);
        $zone = Mockery::mock(Zone::class);

        $partialShipment = new ExpressPartialShipment($shippingCategory, $zone, []);
        $partialShipment->setBaseDeliveryDate($selectedDatetime->modify('-1 day'));

        $validator = Mockery::mock(ValidatorInterface::class);
        $validator->shouldReceive('validate')
                  ->once()
                  ->with($selectedDatetime, Mockery::type(Callback::class))
                  ->andReturn($violationList);

        AbstractPartialShipment::resetId();

        $result = $partialShipment->validate(
            $selectedDatetime,
            $validator,
            Mockery::mock(HolidayServiceInterface::class)
        );

        self::assertFalse($partialShipment->isValid());

        [$violation] = iterator_to_array($result);

        self::assertEquals('shipments.1.deliveryDate', $violation->getPropertyPath());
    }

    public function testValidationConstraintFailIfDeliveryDateTimeIsNotSelected()
    {
        $context = Mockery::mock(ExecutionContextInterface::class);
        $context->shouldReceive('buildViolation')
                ->once()
                ->with('Delivery datetime is not selected.')
                ->andReturnUsing(function () {
                    $builder = Mockery::mock(ConstraintViolationBuilderInterface::class);
                    $builder->shouldReceive('atPath')->once()->with('shipments')->andReturnSelf();
                    $builder->shouldReceive('addViolation')->once()->withNoArgs()->andReturn();

                    return $builder;
                });

        $constraint = $this->getValidationConstraint();
        $callback   = $constraint->callback;

        $callback(null, $context);
    }

    public function testValidationConstraintFailIfSelectedDateIsBeforeThanCalculatedDate()
    {
        $context = Mockery::mock(ExecutionContextInterface::class);
        $context->shouldReceive('buildViolation')
                ->once()
                ->with('Selected date is not valid.')
                ->andReturnUsing(function () {
                    $builder = Mockery::mock(ConstraintViolationBuilderInterface::class);
                    $builder->shouldReceive('atPath')->once()->with('deliveryDate')->andReturnSelf();
                    $builder->shouldReceive('addViolation')->once()->withNoArgs()->andReturn();

                    return $builder;
                });

        $constraint = $this->getValidationConstraint(new DateTimeImmutable());
        $callback   = $constraint->callback;

        $callback(new DateTimeImmutable('-1 day'), $context);
    }

    public function testValidationConstraintFailIfSelectedDateIsInSupplyHolidays()
    {
        $selectedDate = new DateTimeImmutable('2 day');

        $context = Mockery::mock(ExecutionContextInterface::class);
        $context->shouldReceive('buildViolation')
                ->once()
                ->with('Selected date is not valid.')
                ->andReturnUsing(function () {
                    $builder = Mockery::mock(ConstraintViolationBuilderInterface::class);
                    $builder->shouldReceive('atPath')->once()->with('deliveryDate')->andReturnSelf();
                    $builder->shouldReceive('addViolation')->once()->withNoArgs()->andReturn();

                    return $builder;
                });

        $constraint = $this->getValidationConstraint(new DateTimeImmutable());
        $callback   = $constraint->callback;

        $callback($selectedDate, $context);
    }

    public function testValidationConstraintFailIfSelectedDateIsInShipmentHolidays()
    {
        $selectedDate = new DateTimeImmutable('2 day');

        $context = Mockery::mock(ExecutionContextInterface::class);
        $context->shouldReceive('buildViolation')
                ->once()
                ->with('Selected date is not valid.')
                ->andReturnUsing(function () {
                    $builder = Mockery::mock(ConstraintViolationBuilderInterface::class);
                    $builder->shouldReceive('atPath')->once()->with('deliveryDate')->andReturnSelf();
                    $builder->shouldReceive('addViolation')->once()->withNoArgs()->andReturn();

                    return $builder;
                });

        $constraint = $this->getValidationConstraint(new DateTimeImmutable());
        $callback   = $constraint->callback;

        $callback($selectedDate, $context);
    }

    public function testValidationConstraintFailIfSelectedTimeIsNotValid()
    {
        $selectedDate = new DateTimeImmutable('2 day');

        $context = Mockery::mock(ExecutionContextInterface::class);
        $context->shouldReceive('buildViolation')
                ->once()
                ->with('Selected date is not valid.')
                ->andReturnUsing(function () {
                    $builder = Mockery::mock(ConstraintViolationBuilderInterface::class);
                    $builder->shouldReceive('atPath')->once()->with('deliveryDate')->andReturnSelf();
                    $builder->shouldReceive('addViolation')->once()->withNoArgs()->andReturn();

                    return $builder;
                });

        $calculatedDeliveryDate = Mockery::mock(ExpressPartialDeliveryDate::class);
        $calculatedDeliveryDate->shouldReceive('validate')->once()->andReturnFalse();

        $constraint = $this->getValidationConstraint(new DateTimeImmutable(), [
            $calculatedDeliveryDate
        ]);

        $callback = $constraint->callback;

        $callback($selectedDate, $context);
    }

    public function testValidationConstraintPass()
    {
        $selectedDate = new DateTimeImmutable('2 day');

        $context = Mockery::mock(ExecutionContextInterface::class);

        $calculatedDeliveryDate = Mockery::mock(ExpressPartialDeliveryDate::class);
        $calculatedDeliveryDate->shouldReceive('validate')->once()->andReturnTrue();

        $constraint = $this->getValidationConstraint(new DateTimeImmutable(), [
            $calculatedDeliveryDate
        ]);

        $callback = $constraint->callback;

        $callback($selectedDate, $context);
    }

    public function testItDoesNotFreezeIfDeliveryDatesIsNotCalculated()
    {
        $shippingCategory = Mockery::mock(ShippingCategory::class);
        $zone = Mockery::mock(Zone::class);
        $partialShipment = new ExpressPartialShipment($shippingCategory, $zone, []);

        self::expectException(PartialShipmentCanNotBeFreezedException::class);
        self::expectExceptionMessage('Can not freeze partial shipment as delivery dates are not calculated yet!');

        $partialShipment->freeze(new \DateTime());
    }

    private function getValidationConstraint(
        DateTimeImmutable $baseDeliveryDate = null,
        array $calculatedDeliveryDates = null
    ): Constraint {
        $shippingCategory = Mockery::mock(ShippingCategory::class);
        $zone = Mockery::mock(Zone::class);
        $partialShipment = new class ($shippingCategory, $zone, []) extends ExpressPartialShipment {
            public function getValidationConstraints(): Constraint
            {
                return parent::getValidationConstraints();
            }
        };

        if ($baseDeliveryDate) {
            $partialShipment->setBaseDeliveryDate($baseDeliveryDate);
        }

        if ($calculatedDeliveryDates) {
            $partialShipment->setCalculatedDeliveryDates($calculatedDeliveryDates);
        }

        $constraint = $partialShipment->getValidationConstraints();

        self::assertInstanceOf(Callback::class, $constraint);

        return $constraint;
    }
}
