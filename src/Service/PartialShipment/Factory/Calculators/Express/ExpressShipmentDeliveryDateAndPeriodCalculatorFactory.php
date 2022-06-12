<?php

namespace App\Service\PartialShipment\Factory\Calculators\Express;

use App\Service\PartialShipment\Factory\Pipeline\Payload\ConfigureExpressPartialShipmentPayload;

/**
 * Class ExpressShipmentDeliveryDateAndPeriodCalculatorFactory
 */
class ExpressShipmentDeliveryDateAndPeriodCalculatorFactory
{
    /**
     * @var iterable|ExpressShipmentDeliveryDateAndPeriodCalculatorInterface[]
     */
    private iterable $strategies;

    /**
     * ExpressShipmentDeliveryDateAndPeriodCalculatorFactory constructor.
     *
     * @param iterable $strategies
     */
    public function __construct(iterable $strategies)
    {
        $this->strategies = $strategies;
    }

    /**
     * @param ConfigureExpressPartialShipmentPayload $payload
     *
     * @return ExpressShipmentDeliveryDateAndPeriodCalculatorInterface
     */
    public function create(
        ConfigureExpressPartialShipmentPayload $payload
    ): ExpressShipmentDeliveryDateAndPeriodCalculatorInterface {
        foreach ($this->strategies as $strategy) {
            if ($strategy->support($payload)) {
                return $strategy;
            }
        }

        throw new \RuntimeException(sprintf(
            'Unable to create an instance of %s',
            ExpressShipmentDeliveryDateAndPeriodCalculatorInterface::class
        ));
    }
}
