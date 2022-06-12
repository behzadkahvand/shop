<?php

namespace App\Service\Holiday\Adapters;

use App\Dictionary\HolidayTypeDictionary;
use App\Entity\Seller;
use App\Repository\HolidayRepository;

/**
 * Class DoctrineHolidayServiceAdapter
 */
final class DoctrineHolidayServiceAdapter extends AbstractHolidayService
{
    /**
     * @var HolidayRepository
     */
    private HolidayRepository $holidayRepository;

    /**
     * DoctrineHolidayServiceAdapter constructor.
     *
     * @param HolidayRepository $holidayRepository
     */
    public function __construct(HolidayRepository $holidayRepository)
    {
        $this->holidayRepository = $holidayRepository;
    }

    /**
     * @inheritDoc
     */
    public function isOpenForShipment(\DateTimeInterface $dateTime, Seller ...$sellers): bool
    {
        return $this->isOpenFor(HolidayTypeDictionary::HOLIDAY_TYPE_SHIPMENT, $dateTime, ...$sellers);
    }

    /**
     * @inheritDoc
     */
    public function isOpenForSupply(\DateTimeInterface $dateTime, Seller ...$sellers): bool
    {
        return $this->isOpenFor(HolidayTypeDictionary::HOLIDAY_TYPE_SUPPLY, $dateTime, ...$sellers);
    }

    /**
     * @inheritDoc
     */
    public static function getName(): string
    {
        return 'database';
    }

    /**
     * @param int $type
     * @param \DateTimeInterface|string $dateTime
     * @param Seller ...$sellers
     *
     * @return bool
     */
    private function isOpenFor(int $type, $dateTime, Seller ...$sellers): bool
    {
        return $this->holidayRepository->hasHolidayOfType($type, to_date_time($dateTime), ...$sellers);
    }
}
