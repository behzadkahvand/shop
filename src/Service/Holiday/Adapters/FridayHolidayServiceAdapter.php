<?php

namespace App\Service\Holiday\Adapters;

use App\Entity\Seller;
use App\Service\Holiday\HolidayServiceInterface;
use DateTimeInterface;

final class FridayHolidayServiceAdapter extends AbstractHolidayService
{
    public function __construct(private HolidayServiceInterface $inner)
    {
    }

    /**
     * @inheritDoc
     */
    public function isOpenForShipment(DateTimeInterface $dateTime, Seller ...$sellers): bool
    {
        return !$this->isFriday($dateTime) && $this->inner->isOpenForShipment($dateTime, ...$sellers);
    }

    /**
     * @inheritDoc
     */
    public function isOpenForSupply(DateTimeInterface $dateTime, Seller ...$sellers): bool
    {
        return !$this->isFriday($dateTime) && $this->inner->isOpenForSupply($dateTime, ...$sellers);
    }

    /**
     * @inheritDoc
     */
    public static function getName(): string
    {
        return 'friday';
    }

    private function isFriday(DateTimeInterface|string $dateTime): bool
    {
        return 'Friday' === to_date_time($dateTime)->format('l');
    }
}
