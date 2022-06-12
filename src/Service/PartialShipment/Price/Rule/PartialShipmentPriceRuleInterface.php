<?php

namespace App\Service\PartialShipment\Price\Rule;

use App\Service\PartialShipment\ValueObject\PartialShipmentItem;

/**
 * Interface PartialShipmentPriceRuleInterface
 */
interface PartialShipmentPriceRuleInterface
{
    /**
     * @param array|PartialShipmentItem[] $shipmentItem
     * @param int                         $subTotal
     *
     * @return bool
     */
    public function isEligible(array $shipmentItem, int $subTotal): bool;

    /**
     * @param array|PartialShipmentItem[] $shipmentItem
     * @param int   $subTotal
     *
     * @return int
     */
    public function addToGrandTotal(array $shipmentItem, int $subTotal): int;
}
