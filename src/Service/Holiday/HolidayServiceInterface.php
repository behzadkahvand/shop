<?php

namespace App\Service\Holiday;

use App\Entity\Seller;

/**
 * Interface HolidayServiceInterface
 */
interface HolidayServiceInterface
{
    /**
     * @param \DateTimeInterface|string $dateTime Datetime instance or a date time string
     * @param Seller ...$sellers
     *
     * @return bool
     */
    public function isOpenForShipment(\DateTimeInterface $dateTime, Seller ...$sellers): bool;

    /**
     * @param \DateTimeInterface $dateTime
     * @param Seller ...$sellers
     *
     * @return bool
     */
    public function isOpenForSupply(\DateTimeInterface $dateTime, Seller ...$sellers): bool;

    /**
     * @param \DateTimeInterface $dateTime
     * @param Seller ...$sellers
     *
     * @return \DateTimeInterface
     */
    public function getFirstOpenSupplyDateSince(\DateTimeInterface $dateTime, Seller ...$sellers): \DateTimeInterface;

    /**
     * @param \DateTimeInterface $dateTime
     * @param Seller ...$sellers
     *
     * @return \DateTimeInterface
     */
    public function getFirstOpenShipmentDateSince(\DateTimeInterface $dateTime, Seller ...$sellers): \DateTimeInterface;

    /**
     * @return string
     */
    public static function getName(): string;
}
