<?php

namespace App\Service\Holiday\Adapters;

use App\Entity\Seller;
use App\Service\Holiday\HolidayServiceInterface;

/**
 * Class CallbackHolidayServiceAdapter
 */
final class CallbackHolidayServiceAdapter implements HolidayServiceInterface
{
    /**
     * @var callable
     */
    private $callable;

    /**
     * CallbackHolidayServiceAdapter constructor.
     *
     * @param callable|null $callableHolidayCalculator
     */
    public function __construct(?callable $callableHolidayCalculator)
    {
        $this->callable = $callableHolidayCalculator;
    }

    /**
     * @inheritDoc
     */
    public function isOpenForShipment(\DateTimeInterface $dateTime, Seller ...$sellers): bool
    {
        return (bool) ($this->callable)($dateTime);
    }

    /**
     * @inheritDoc
     */
    public function isOpenForSupply(\DateTimeInterface $dateTime, Seller ...$sellers): bool
    {
        return (bool) ($this->callable)($dateTime);
    }

    /**
     * @inheritDoc
     */
    public function getFirstOpenShipmentDateSince(\DateTimeInterface $dateTime, Seller ...$sellers): \DateTimeInterface
    {
        return $dateTime;
    }

    /**
     * @inheritDoc
     */
    public function getFirstOpenSupplyDateSince(\DateTimeInterface $dateTime, Seller ...$sellers): \DateTimeInterface
    {
        return $dateTime;
    }

    /**
     * @inheritDoc
     */
    public static function getName(): string
    {
        return 'callback';
    }
}
