<?php

namespace App\Service\PartialShipment\ValueObject;

/**
 * Class DeliveryDateAndPeriodCalculatorResult
 */
final class DeliveryDateAndPeriodCalculatorResult
{
    private \DateTimeImmutable $baseDeliveryDate;

    private array $periods;

    /**
     * DeliveryDateAndPeriodCalculatorResult constructor.
     *
     * @param \DateTimeImmutable $baseDeliveryDate
     * @param array $periods
     */
    public function __construct(\DateTimeImmutable $baseDeliveryDate, array $periods)
    {
        $this->baseDeliveryDate = $baseDeliveryDate;
        $this->periods = $periods;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getBaseDeliveryDate(): \DateTimeImmutable
    {
        return $this->baseDeliveryDate;
    }

    /**
     * @return array
     */
    public function getPeriods(): array
    {
        return $this->periods;
    }
}
