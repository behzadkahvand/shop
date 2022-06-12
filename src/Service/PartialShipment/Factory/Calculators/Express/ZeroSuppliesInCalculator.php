<?php

namespace App\Service\PartialShipment\Factory\Calculators\Express;

use App\Dictionary\ConfigurationCodeDictionary;
use App\Service\Configuration\ConfigurationServiceInterface;
use App\Service\Holiday\HolidayServiceInterface;
use App\Service\PartialShipment\Factory\Pipeline\Payload\ConfigureExpressPartialShipmentPayload;
use App\Service\PartialShipment\ValueObject\DeliveryDateAndPeriodCalculatorResult;
use DateTimeImmutable;
use DateTimeInterface;

/**
 * Class ZeroSuppliesInStrategy
 */
final class ZeroSuppliesInCalculator implements ExpressShipmentDeliveryDateAndPeriodCalculatorInterface
{
    private const WAREHOUSE_PROCESSING_DURATION_IN_HOUR = 6;
    private const WAREHOUSE_START_TIME = '09:00:00';
    private const WAREHOUSE_END_TIME = '20:59:59';

    private HolidayServiceInterface $holidayService;

    private ConfigurationServiceInterface $configurationService;

    /**
     * ZeroSuppliesInStrategy constructor.
     *
     * @param HolidayServiceInterface $holidayService
     * @param ConfigurationServiceInterface $configurationService
     */
    public function __construct(
        HolidayServiceInterface $holidayService,
        ConfigurationServiceInterface $configurationService
    ) {
        $this->holidayService       = $holidayService;
        $this->configurationService = $configurationService;
    }

    /**
     * @inheritDoc
     */
    public static function getPriority(): int
    {
        return 1;
    }

    /**
     * @inheritDoc
     */
    public function support(ConfigureExpressPartialShipmentPayload $payload): bool
    {
        return $payload->getPartialShipment()->suppliesInIsZero();
    }

    /**
     * @inheritDoc
     */
    public function calculate(
        ConfigureExpressPartialShipmentPayload $payload,
        array $periods
    ): DeliveryDateAndPeriodCalculatorResult {
        $baseDeliveryDate = to_date_time_immutable($payload->getPartialShipment()->getBaseDeliveryDate());
        $orderDatetime    = to_date_time_immutable($payload->getOrderDatetime());

        if ($this->isBeforeWarehouseWorkingHour($orderDatetime)) {
            $warehouseStartTime = array_map(static fn($v) => (int) $v, explode(':', $this->getWareHouseStartTime()));
            $baseDeliveryDate   = $baseDeliveryDate->setTime(...$warehouseStartTime);
        } elseif ($this->isAfterWarehouseWorkingHour($orderDatetime)) {
            $warehouseStartTime = array_map(static fn($v) => (int) $v, explode(':', $this->getWareHouseStartTime()));
            $baseDeliveryDate   = $baseDeliveryDate->modify('1 day')->setTime(...$warehouseStartTime);
        }

        $readyForSendDatetime = $this->addWarehouseProcessingDuration($baseDeliveryDate);
        $periodsCount         = count($periods);
        $closedPeriodsCount   = 0;

        foreach ($periods as $i => $period) {
            if (false === $period->isOpenForDelivery($readyForSendDatetime)) {
                $periods[$i] = $period->setIsSelectable(false);

                $closedPeriodsCount++;
            }
        }

        if ($this->allPeriodsAreClosed($periodsCount, $closedPeriodsCount)) {
            $periods          = array_map(static fn($p) => $p->setIsSelectable(true), $periods);
            $baseDeliveryDate = $this->getFirstOpenShipmentDateSince($baseDeliveryDate->modify('1 day'));
        }

        return new DeliveryDateAndPeriodCalculatorResult(
            $this->getFirstOpenShipmentDateSince($baseDeliveryDate),
            $periods
        );
    }

    /**
     * @param DateTimeImmutable $orderDatetime
     *
     * @return bool
     */
    private function isBeforeWarehouseWorkingHour(DateTimeImmutable $orderDatetime): bool
    {
        return $orderDatetime->format('H:i:s') < $this->getWareHouseStartTime();
    }

    /**
     * @param DateTimeImmutable $orderDatetime
     *
     * @return bool
     */
    private function isAfterWarehouseWorkingHour(DateTimeImmutable $orderDatetime): bool
    {
        return $this->getWarehouseEndTime() < $orderDatetime->format('H:i:s');
    }

    /**
     * @param DateTimeImmutable $dateTime
     *
     * @return DateTimeInterface
     */
    private function getFirstOpenShipmentDateSince(DateTimeImmutable $dateTime): DateTimeInterface
    {
        return $this->holidayService->getFirstOpenShipmentDateSince($dateTime);
    }

    /**
     * @param int $periodsCount
     * @param int $closedPeriodsCount
     *
     * @return bool
     */
    private function allPeriodsAreClosed(int $periodsCount, int $closedPeriodsCount): bool
    {
        return 0 < $periodsCount && $periodsCount === $closedPeriodsCount;
    }

    /**
     * @param DateTimeImmutable $orderDatetime
     *
     * @return mixed
     */
    private function addWarehouseProcessingDuration(DateTimeImmutable $orderDatetime)
    {
        return $orderDatetime->modify($this->getWarehouseProcessingDurationInHour() . ' hour');
    }

    /**
     * @return string
     */
    private function getWareHouseStartTime(): string
    {
        $warehouseStartTime = $this->getConfig(ConfigurationCodeDictionary::WAREHOUSE_START_TIME);

        try {
            return to_date_time($warehouseStartTime)->format('H:i:s');
        } catch (\InvalidArgumentException $e) {
            return self::WAREHOUSE_START_TIME;
        }
    }

    /**
     * @return int
     */
    private function getWarehouseProcessingDurationInHour(): int
    {
        $warehouseProcessingDurationInHour = $this->getConfig(
            ConfigurationCodeDictionary::WAREHOUSE_PROCESSING_DURATION_IN_HOUR
        );

        if (is_numeric($warehouseProcessingDurationInHour)) {
            return (int) $warehouseProcessingDurationInHour;
        }

        return self::WAREHOUSE_PROCESSING_DURATION_IN_HOUR;
    }

    /**
     * @return string
     */
    private function getWarehouseEndTime(): string
    {
        $warehouseEndTime = $this->getConfig(ConfigurationCodeDictionary::WAREHOUSE_END_TIME);

        try {
            return to_date_time($warehouseEndTime)->format('H:i:s');
        } catch (\InvalidArgumentException $e) {
            return self::WAREHOUSE_END_TIME;
        }
    }

    /**
     * @param string $code
     *
     * @return int|string|null
     */
    private function getConfig(string $code)
    {
        if (!isset($this->configs)) {
            $this->configs = $this->configurationService->findByCodes(
                ConfigurationCodeDictionary::WAREHOUSE_START_TIME,
                ConfigurationCodeDictionary::WAREHOUSE_PROCESSING_DURATION_IN_HOUR,
                ConfigurationCodeDictionary::WAREHOUSE_END_TIME,
            );
        }

        if (null !== $this->configs[$code]) {
            return $this->configs[$code]->getValue();
        }

        return null;
    }
}
