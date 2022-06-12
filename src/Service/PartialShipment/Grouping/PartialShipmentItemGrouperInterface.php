<?php

namespace App\Service\PartialShipment\Grouping;

use App\Service\PartialShipment\ValueObject\PartialShipmentItem;

/**
 * Interface PartialShipmentItemGrouperInterface
 */
interface PartialShipmentItemGrouperInterface
{
    /**
     * @param array|PartialShipmentItem[] $items
     *
     * @return iterable
     */
    public function group(array $items): iterable;
}
