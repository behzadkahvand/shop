<?php

namespace App\Service\PartialShipment\Price;

use App\Entity\ShippingCategory;
use App\Entity\ShippingMethodPrice;
use App\Entity\Zone;
use App\Repository\ShippingMethodPriceRepository;
use App\Service\PartialShipment\Exceptions\MinimumShipmentItemCountException;
use App\Service\PartialShipment\Price\Rule\PartialShipmentPriceRuleInterface;
use App\Service\PartialShipment\ValueObject\PartialShipmentPrice;
use App\Service\PartialShipment\ValueObject\PartialShipmentShippingMethodAndPriceCalculationResult;

/**
 * Class PartialShipmentShippingMethodAndPriceCalculator
 */
class PartialShipmentShippingMethodAndPriceCalculator
{
    /**
     * @var ShippingMethodPriceRepository
     */
    private ShippingMethodPriceRepository $shippingMethodPriceRepository;

    /**
     * @var iterable|PartialShipmentPriceRuleInterface[]
     */
    private iterable $rules;

    /**
     * PartialShipmentShippingMethodAndPriceCalculator constructor.
     *
     * @param ShippingMethodPriceRepository $shippingMethodPriceRepository
     * @param iterable                      $rules
     */
    public function __construct(ShippingMethodPriceRepository $shippingMethodPriceRepository, iterable $rules)
    {
        $this->shippingMethodPriceRepository = $shippingMethodPriceRepository;
        $this->rules                         = $rules;
    }

    /**
     * @param ShippingCategory $category
     * @param Zone             $zone
     * @param array            $shipmentItems
     *
     * @return PartialShipmentShippingMethodAndPriceCalculationResult
     */
    public function calculate(
        ShippingCategory $category,
        Zone $zone,
        array $shipmentItems
    ): PartialShipmentShippingMethodAndPriceCalculationResult {
        if (empty($shipmentItems)) {
            throw new MinimumShipmentItemCountException(sprintf('%s() needs at least one shipment item', __METHOD__));
        }

        $shippingMethodPrice = $this->getShippingMethodPriceByShippingCategoryAndZone($category, $zone);
        $method              = $shippingMethodPrice->getShippingMethod();

        $subTotal   = $shippingMethodPrice->getPrice();
        $grandTotal = $subTotal;

        foreach ($this->rules as $rule) {
            if ($rule->isEligible($shipmentItems, $subTotal)) {
                $grandTotal += $rule->addToGrandTotal($shipmentItems, $subTotal);
            }
        }

        $grandTotal = 0 <= $grandTotal ? $grandTotal : 0;

        return new PartialShipmentShippingMethodAndPriceCalculationResult(
            $method,
            new PartialShipmentPrice($subTotal, $grandTotal)
        );
    }

    /**
     * @param      $category
     * @param Zone $zone
     *
     * @return ShippingMethodPrice
     */
    private function getShippingMethodPriceByShippingCategoryAndZone($category, Zone $zone): ShippingMethodPrice
    {
        return $this->shippingMethodPriceRepository->getPriceByShippingCategoryAndZone($category, $zone);
    }
}
