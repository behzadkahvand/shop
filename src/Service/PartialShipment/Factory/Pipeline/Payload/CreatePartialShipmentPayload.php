<?php

namespace App\Service\PartialShipment\Factory\Pipeline\Payload;

use App\Entity\Zone;
use App\Service\PartialShipment\Types\AbstractPartialShipment;
use App\Service\Pipeline\AbstractPipelinePayload;
use DateTimeImmutable;

/**
 * Class CreatePartialShipmentPayload
 */
final class CreatePartialShipmentPayload extends AbstractPipelinePayload
{
    /**
     * @var AbstractPartialShipment
     */
    private AbstractPartialShipment $partialShipment;

    /**
     * @var Zone
     */
    private Zone $zone;

    /**
     * @var DateTimeImmutable
     */
    private DateTimeImmutable $orderDatetime;

    /**
     * CreatePartialShipmentPayload constructor.
     *
     * @param AbstractPartialShipment $partialShipment
     * @param Zone $zone
     * @param DateTimeImmutable $orderDatetime
     */
    public function __construct(
        AbstractPartialShipment $partialShipment,
        Zone $zone,
        DateTimeImmutable $orderDatetime
    ) {
        $this->partialShipment = $partialShipment;
        $this->zone            = $zone;
        $this->orderDatetime   = $orderDatetime;
    }

    /**
     * @return AbstractPartialShipment
     */
    public function getPartialShipment(): AbstractPartialShipment
    {
        return $this->partialShipment;
    }

    /**
     * @return Zone
     */
    public function getZone(): Zone
    {
        return $this->zone;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getOrderDatetime(): DateTimeImmutable
    {
        return $this->orderDatetime;
    }
}
