<?php

namespace App\Service\Holiday\Adapters;

use App\Entity\Seller;
use App\Service\Holiday\HolidayServiceInterface;

/**
 * Class AbstractHolidayService
 */
abstract class AbstractHolidayService implements HolidayServiceInterface
{
    /**
     * @inheritDoc
     */
    public function getFirstOpenShipmentDateSince(\DateTimeInterface $dateTime, Seller ...$sellers): \DateTimeInterface
    {
        $dateTime = to_date_time_immutable($dateTime->format('Y-m-d H:i:s'));

        while (!$this->isOpenForShipment($dateTime, ...$sellers)) {
            $dateTime = $dateTime->modify('1 day');
        }

        if (0 === count($sellers) || $this->isOpenForShipment($dateTime)) {
            return $dateTime;
        }

        return $this->getFirstOpenShipmentDateSince($dateTime->modify('1 day'), ...$sellers);
    }

    /**
     * @inheritDoc
     */
    public function getFirstOpenSupplyDateSince(\DateTimeInterface $dateTime, Seller ...$sellers): \DateTimeInterface
    {
        $dateTime = to_date_time_immutable($dateTime->format('Y-m-d H:i:s'));

        if (0 === count($sellers)) {
            while (!$this->isOpenForSupply($dateTime)) {
                $dateTime = $dateTime->modify('1 day');
            }

            return $dateTime;
        }

        while (true) {
            foreach ($sellers as $seller) {
                if ($this->isOpenForSupply($dateTime, $seller)) {
                    return $dateTime;
                }
            }

            $dateTime = $dateTime->modify('1 day');
        }
    }
}
