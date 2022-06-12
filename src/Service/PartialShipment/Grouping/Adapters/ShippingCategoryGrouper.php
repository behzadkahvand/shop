<?php

namespace App\Service\PartialShipment\Grouping\Adapters;

use App\Service\PartialShipment\Grouping\PartialShipmentItemGrouperInterface;
use App\Service\PartialShipment\ValueObject\PartialShipmentItem;

/**
 * Class ShippingCategoryGrouper
 */
final class ShippingCategoryGrouper implements PartialShipmentItemGrouperInterface
{
    /**
     * @inheritDoc
     */
    public function group(array $items): iterable
    {
        $callback = fn(PartialShipmentItem $item) => [$item->getShippingCategoryId() => $item];

        return collect($items)->mapToGroups($callback)->toArray();
    }
}
