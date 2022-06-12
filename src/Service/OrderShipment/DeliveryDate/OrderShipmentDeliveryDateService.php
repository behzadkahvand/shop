<?php

namespace App\Service\OrderShipment\DeliveryDate;

use App\Dictionary\CityDictionary;
use App\Entity\OrderShipment;
use App\Entity\ShippingPeriod;
use App\Repository\ShippingPeriodRepository;
use App\Service\Holiday\HolidayServiceInterface;
use App\Service\PartialShipment\ValueObject\PartialShipmentPeriod;

final class OrderShipmentDeliveryDateService
{
    private HolidayServiceInterface $holidayService;

    private ShippingPeriodRepository $shippingPeriodRepository;

    public function __construct(
        HolidayServiceInterface $holidayService,
        ShippingPeriodRepository $shippingPeriodRepository
    ) {
        $this->holidayService = $holidayService;
        $this->shippingPeriodRepository = $shippingPeriodRepository;
    }

    public function getDeliveryDatesForShipment(OrderShipment $shipment, int $resultsCount = 10): array
    {
        $beginning = new \DateTimeImmutable($shipment->getDeliveryDate()->format('Y-m-d'));
        $resultsCount = 0 !== $resultsCount ? abs($resultsCount) : 10;
        $dates = [];
        $periods = $this->getPeriods($shipment);

        while (count($dates) < $resultsCount) {
            if ($this->holidayService->isOpenForShipment($beginning)) {
                $dates[] = $beginning->format('Y-m-d');
            }

            $beginning = $beginning->modify('1 day');
        }

        return compact('periods', 'dates');
    }

    public function isValid(OrderShipment $shipment, $dateTime): bool
    {
        $dateTime = $dateTime instanceof \DateTimeInterface ? $dateTime : new \DateTimeImmutable($dateTime);

        $periods = array_map(fn(ShippingPeriod $p) => $p->getStart()->format('H:i'), $this->getActivePeriods());

        $greaterThanShipmentDate = new \DateTimeImmutable('now') <= $dateTime;
        $periodTimeIsValid       = in_array($dateTime->format('H:i'), $periods);
        $isOpenForShipment       = $this->holidayService->isOpenForShipment($dateTime);

        return $greaterThanShipmentDate && $periodTimeIsValid && $isOpenForShipment;
    }

    private function getPeriods(OrderShipment $shipment): array
    {
        $city = $shipment->getOrder()?->getOrderAddress()?->getCity()?->getName();

        $periods = [];
        if (in_array($city, CityDictionary::EXPRESS_CITIES)) {
            $periods = array_map(
                [PartialShipmentPeriod::class, 'fromShippingPeriod'],
                $this->getActivePeriods()
            );
        }

        return $periods;
    }

    /**
     * @return ShippingPeriod[]|array
     */
    private function getActivePeriods()
    {
        return $this->shippingPeriodRepository->findBy(['isActive' => true]);
    }
}
