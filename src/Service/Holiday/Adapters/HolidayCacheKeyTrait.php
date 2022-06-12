<?php

namespace App\Service\Holiday\Adapters;

use App\Entity\Seller;

/**
 * Trait HolidayCacheKeyTrait
 */
trait HolidayCacheKeyTrait
{
    /**
     * @param \DateTimeInterface $dateTime
     * @param Seller|null $seller
     *
     * @return string
     */
    private function shipmentKey(\DateTimeInterface $dateTime, Seller $seller = null): string
    {
        return sprintf('holiday_shipment_%s%s', $seller ? "seller_{$seller->getId()}_" : '', $dateTime->format('Ymd'));
    }

    /**
     * @param \DateTimeInterface $dateTime
     * @param Seller|null $seller
     *
     * @return string
     */
    private function supplyKey(\DateTimeInterface $dateTime, Seller $seller = null): string
    {
        return sprintf('holiday_supply_%s%s', $seller ? "seller_{$seller->getId()}_" : '', $dateTime->format('Ymd'));
    }
}
