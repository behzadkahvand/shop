<?php

namespace App\Service\PartialShipment\Factory\Pipeline\Stages;

use App\Service\PartialShipment\Factory\Pipeline\Payload\CreatePartialShipmentPayload;
use App\Service\PartialShipment\Price\PartialShipmentShippingMethodAndPriceCalculator;
use App\Service\PartialShipment\Types\AbstractPartialShipment;
use App\Service\Pipeline\AbstractPipelinePayload;
use App\Service\Pipeline\PayloadAwarePipelineStageInterface;

/**
 * Class CalculateShippingMethodAndPriceStage
 */
final class CalculateShippingMethodAndPriceStage implements PayloadAwarePipelineStageInterface
{
    private PartialShipmentShippingMethodAndPriceCalculator $shippingPriceCalculator;

    /**
     * CalculateShippingMethodAndPriceStage constructor.
     *
     * @param PartialShipmentShippingMethodAndPriceCalculator $shippingPriceCalculator
     */
    public function __construct(PartialShipmentShippingMethodAndPriceCalculator $shippingPriceCalculator)
    {
        $this->shippingPriceCalculator = $shippingPriceCalculator;
    }

    public function __invoke(AbstractPipelinePayload $payload)
    {
        /** @var AbstractPartialShipment $partialShipment */
        $partialShipment = $payload->getPartialShipment();

        $items    = $partialShipment->getShipmentItems();
        $category = $partialShipment->getShippingCategory();
        $result   = $this->shippingPriceCalculator->calculate($category, $payload->getZone(), $items);

        $partialShipment->setShippingMethod($result->getShippingMethod());
        $partialShipment->setPrice($result->getPrice());

        return $payload;
    }

    /**
     * @return string
     */
    public static function getSupportedPayload(): string
    {
        return CreatePartialShipmentPayload::class;
    }

    /**
     * @return int
     */
    public static function getPriority(): int
    {
        return 90;
    }
}
