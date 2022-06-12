<?php

namespace App\Service\PartialShipment\Factory\Calculators\Express;

use App\Service\PartialShipment\Factory\Pipeline\Payload\ConfigureExpressPartialShipmentPayload;
use App\Service\PartialShipment\ValueObject\DeliveryDateAndPeriodCalculatorResult;
use App\Service\PartialShipment\ValueObject\PartialShipmentPeriod;

/**
 * Interface ExpressShipmentDeliveryDateAndPeriodCalculatorInterface
 */
interface ExpressShipmentDeliveryDateAndPeriodCalculatorInterface
{
    /**
     * @return int
     */
    public static function getPriority(): int;

    /**
     * @param ConfigureExpressPartialShipmentPayload $payload
     *
     * @return bool
     */
    public function support(ConfigureExpressPartialShipmentPayload $payload): bool;

    /**
     * @param ConfigureExpressPartialShipmentPayload $payload
     * @param array|PartialShipmentPeriod[] $periods
     *
     * @return DeliveryDateAndPeriodCalculatorResult
     */
    public function calculate(
        ConfigureExpressPartialShipmentPayload $payload,
        array $periods
    ): DeliveryDateAndPeriodCalculatorResult;
}
