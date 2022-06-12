<?php

namespace App\Tests\Unit\Service\PartialShipment\Types;

use App\Entity\ShippingCategory;
use App\Entity\ShippingMethod;
use App\Entity\ShippingPeriod;
use App\Entity\Zone;
use App\Service\PartialShipment\Exceptions\PartialShipmentCanNotBeFreezedException;
use App\Service\PartialShipment\Types\AbstractPartialShipment;
use App\Service\PartialShipment\Types\ValidatablePartialShipmentInterface;
use App\Service\PartialShipment\ValueObject\ExpressPartialDeliveryDate;
use App\Service\PartialShipment\ValueObject\PartialShipmentItem;
use App\Service\PartialShipment\ValueObject\PartialShipmentPeriod;
use App\Service\PartialShipment\ValueObject\PartialShipmentPrice;
use DateTimeInterface;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class AbstractPartialShipmentTest
 */
final class AbstractPartialShipmentTest extends MockeryTestCase
{
    public function testItGetsSuppliesIn(): void
    {
        $suppliesIn = 1;

        $item = Mockery::mock(PartialShipmentItem::class);
        $item->shouldReceive('getSuppliesIn')->once()->withNoArgs()->andReturn($suppliesIn);
        $shippingCategory = Mockery::mock(ShippingCategory::class);
        $zone = Mockery::mock(Zone::class);

        $partialShipment = new class ($shippingCategory, $zone, [$item]) extends AbstractPartialShipment {
        };

        self::assertEquals($suppliesIn, $partialShipment->getItemsMaxSuppliesIn());
    }

    public function testItThrowExceptionIfItsValidatableButIsNotValid(): void
    {
        $shippingCategory = Mockery::mock(ShippingCategory::class);
        $zone = Mockery::mock(Zone::class);
        $partialShipment = new class ($shippingCategory, $zone, []) extends AbstractPartialShipment implements ValidatablePartialShipmentInterface {
            public function isValid(): bool
            {
                return false;
            }
            public function validate(
                DateTimeInterface $selectedDatetime,
                ValidatorInterface $validator
            ): ConstraintViolationListInterface {
            }
        };

        $this->expectException(PartialShipmentCanNotBeFreezedException::class);
        $this->expectExceptionMessage(
            sprintf('Calling %s::validate() before freezing it is necessary.', get_class($partialShipment))
        );

        $partialShipment->freeze(new \DateTime());
    }

    public function testItFreeze(): void
    {
        $baseDeliveryDate = new \DateTimeImmutable();

        $shippingCategory = Mockery::mock(ShippingCategory::class);
        $shippingCategory->shouldReceive('getName')->once()->withNoArgs()->andReturn('name');

        $item = Mockery::mock(PartialShipmentItem::class);
        $item->shouldReceive('getShippingCategory')->once()->withNoArgs()->andReturn($shippingCategory);
        $zone = Mockery::mock(Zone::class);

        $partialShipment = new class ($shippingCategory, $zone, [$item]) extends AbstractPartialShipment {
        };

        $shippingPeriod1 = Mockery::mock(ShippingPeriod::class);
        $shippingPeriod1->shouldReceive(['getId' => 1, 'getStart' => $baseDeliveryDate, 'getEnd' => $baseDeliveryDate])
                        ->once()
                        ->withNoArgs();

        $shippingPeriod2 = Mockery::mock(ShippingPeriod::class);
        $shippingPeriod2->shouldReceive([
                            'getId' => 1,
                            'getStart' => $baseDeliveryDate->modify('1 day'),
                            'getEnd' => $baseDeliveryDate->modify('1 day')
                        ])
                        ->once()
                        ->withNoArgs();

        $partialShipment->setBaseDeliveryDate($baseDeliveryDate);
        $partialShipment->setShippingMethod(Mockery::mock(ShippingMethod::class));
        $partialShipment->setPrice(new PartialShipmentPrice(1000, 1000));
        $partialShipment->setCalculatedDeliveryDates([
            new ExpressPartialDeliveryDate(
                $baseDeliveryDate,
                [new PartialShipmentPeriod($shippingPeriod1)]
            ),
            new ExpressPartialDeliveryDate(
                $baseDeliveryDate->modify('1 day'),
                [new PartialShipmentPeriod($shippingPeriod2)]
            ),
        ]);

        $partialShipment->freeze($baseDeliveryDate);
    }
}
