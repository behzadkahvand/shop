<?php

namespace App\Service\PartialShipment\Price\Rule\Adapters;

use App\Service\PartialShipment\Price\Rule\PartialShipmentPriceRuleInterface;
use App\Service\PartialShipment\ValueObject\PartialShipmentItem;

/**
 * Class ExpressDeliveryPriceRule
 */
final class ExpressDeliveryPriceRule implements PartialShipmentPriceRuleInterface
{
    /**
     * @inheritDoc
     */
    public function isEligible(array $shipmentItem, int $subTotal): bool
    {
        return 0 < count($shipmentItem) && 200000 < $this->getTotalPrice($shipmentItem);
    }

    /**
     * @inheritDoc
     */
    public function addToGrandTotal(array $inventories, int $subTotal): int
    {
        return (-1 * $subTotal);
    }

    /**
     * @param array $inventories
     *
     * @return float|int
     */
    private function getTotalPrice(array $inventories)
    {
        return array_sum(array_map(fn(PartialShipmentItem $i) => $i->getPrice(), $inventories));
    }
}
