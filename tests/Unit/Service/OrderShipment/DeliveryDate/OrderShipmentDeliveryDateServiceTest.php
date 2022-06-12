<?php

namespace App\Tests\Unit\Service\OrderShipment\DeliveryDate;

use App\Dictionary\CityDictionary;
use App\Entity\City;
use App\Entity\Order;
use App\Entity\OrderAddress;
use App\Entity\OrderShipment;
use App\Entity\ShippingPeriod;
use App\Repository\ShippingPeriodRepository;
use App\Service\Holiday\Adapters\CallbackHolidayServiceAdapter;
use App\Service\OrderShipment\DeliveryDate\OrderShipmentDeliveryDateService;
use DateTimeImmutable;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class OrderShipmentDeliveryDateServiceTest
 */
final class OrderShipmentDeliveryDateServiceTest extends MockeryTestCase
{
    //@TODO Fix test for coverage
    public function testItGetsDeliveryDateForOrderShipment()
    {
        $city = new City();
        $city->setName(CityDictionary::TEHRAN_NAME);
        $orderAddress = new OrderAddress();
        $orderAddress->setCity($city);
        $order = new Order();
        $order->addOrderAddress($orderAddress);
        $orderShipment = new OrderShipment();
        $orderShipment->setDeliveryDate(new DateTimeImmutable());
        $orderShipment->setOrder($order);

        $shippingPeriodRepository = Mockery::mock(ShippingPeriodRepository::class);

        $period = new class () extends ShippingPeriod {
            public function getStart()
            {
                return new DateTimeImmutable();
            }

            public function getEnd()
            {
                return new DateTimeImmutable();
            }

            public function getId(): ?int
            {
                return random_int(1, 100);
            }
        };

        $shippingPeriodRepository->shouldReceive('findBy')
            ->once()
            ->with(['isActive' => true])
            ->andReturn([$period, $period]);

        $holidayService = new CallbackHolidayServiceAdapter(function () {
            return (bool) random_int(0, 1);
        });

        $service = new OrderShipmentDeliveryDateService($holidayService, $shippingPeriodRepository);

        $expectedCount = 10;
        $results = $service->getDeliveryDatesForShipment($orderShipment, $expectedCount);

        self::assertArrayHasKey('periods', $results);
        self::assertArrayHasKey('dates', $results);

        self::assertCount($expectedCount, $results['dates']);
    }

    public function testItValidateGivenDateTimeForGivenOrderShipment()
    {
        $orderShipment = new OrderShipment();
        $friday = new DateTimeImmutable('2020-03-20');
        $orderShipment->setDeliveryDate(new DateTimeImmutable());

        $shippingPeriod = Mockery::mock(ShippingPeriod::class);
        $shippingPeriod->shouldReceive('getStart')
                       ->times(4)
                       ->withNoArgs()
                       ->andReturn(new DateTimeImmutable('09:00'));

        $shippingPeriodRepository = Mockery::mock(ShippingPeriodRepository::class);
        $shippingPeriodRepository->shouldReceive('findBy')
                                 ->times(4)
                                 ->with(['isActive' => true])
                                 ->andReturn([$shippingPeriod]);

        $holidayService = new CallbackHolidayServiceAdapter(function () {
            return (bool) random_int(0, 1);
        });
        $service = new OrderShipmentDeliveryDateService($holidayService, $shippingPeriodRepository);

        self::assertFalse($service->isValid($orderShipment, $friday));

        $deliveryDate = new DateTimeImmutable('now');
        $orderShipment->setDeliveryDate($deliveryDate);

        self::assertFalse($service->isValid($orderShipment, $deliveryDate->modify('-1 minute')));

        $holidayService = new CallbackHolidayServiceAdapter('always_false_function');

        $service = new OrderShipmentDeliveryDateService($holidayService, $shippingPeriodRepository);

        self::assertFalse($service->isValid($orderShipment, $deliveryDate->modify('1 day')));

        $holidayService = new CallbackHolidayServiceAdapter('always_true_function');

        $service = new OrderShipmentDeliveryDateService($holidayService, $shippingPeriodRepository);

        self::assertTrue($service->isValid($orderShipment, $deliveryDate->modify('1 day')->setTime(9, 0)));
    }
}
