<?php

namespace App\Service\PartialShipment\Factory\Calculators\Express;

use App\Service\Holiday\HolidayServiceInterface;
use App\Service\PartialShipment\Factory\Pipeline\Payload\ConfigureExpressPartialShipmentPayload;
use App\Service\PartialShipment\ValueObject\DeliveryDateAndPeriodCalculatorResult;
use DateTimeImmutable;

/**
 * Class NoneZeroSuppliesInStrategy
 */
final class NoneZeroSuppliesInCalculator implements ExpressShipmentDeliveryDateAndPeriodCalculatorInterface
{
    public function __construct(private HolidayServiceInterface $holidayService)
    {
    }

    /**
     * @inheritDoc
     */
    public static function getPriority(): int
    {
        return 2;
    }

    /**
     * @inheritDoc
     */
    public function support(ConfigureExpressPartialShipmentPayload $payload): bool
    {
        return !$payload->getPartialShipment()->suppliesInIsZero();
    }

    /**
     * @inheritDoc
     */
    public function calculate(
        ConfigureExpressPartialShipmentPayload $payload,
        array $periods
    ): DeliveryDateAndPeriodCalculatorResult {
        $baseDeliveryDate = $this->applyWarehouseProcessingDuration(
            $payload->getPartialShipment()->getBaseDeliveryDate()
        );

        return new DeliveryDateAndPeriodCalculatorResult($baseDeliveryDate, $periods);
    }

    /**
     * @param DateTimeImmutable $baseDeliveryDate
     *
     * @return DateTimeImmutable
     */
    private function applyWarehouseProcessingDuration(DateTimeImmutable $baseDeliveryDate): DateTimeImmutable
    {
        return
            DateTimeImmutable::createFromInterface(
                $this->holidayService->getFirstOpenShipmentDateSince($baseDeliveryDate->modify('1 day'))
            );
    }
}
