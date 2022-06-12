<?php

namespace App\Service\PartialShipment\Factory\Pipeline\Payload;

use App\Service\PartialShipment\Types\AbstractPartialShipment;
use App\Service\Pipeline\AbstractPipelinePayload;
use DateTimeImmutable;

/**
 * Class ConfigureExpressPartialShipmentPayload
 */
final class ConfigureExpressPartialShipmentPayload extends AbstractPipelinePayload
{
    /**
     * @var AbstractPartialShipment
     */
    private AbstractPartialShipment $partialShipment;

    /**
     * @var DateTimeImmutable
     */
    private DateTimeImmutable $orderDatetime;

    /**
     * ConfigureExpressPartialShipmentPayload constructor.
     *
     * @param AbstractPartialShipment $partialShipment
     * @param DateTimeImmutable $orderDatetime
     */
    public function __construct(AbstractPartialShipment $partialShipment, DateTimeImmutable $orderDatetime)
    {
        $this->partialShipment = $partialShipment;
        $this->orderDatetime   = $orderDatetime;
    }

    /**
     * @param CreatePartialShipmentPayload $payload
     *
     * @return static
     */
    public static function fromCreatePartialShipmentPayload(CreatePartialShipmentPayload $payload): self
    {
        return new self($payload->getPartialShipment(), $payload->getOrderDatetime());
    }

    /**
     * @return AbstractPartialShipment
     */
    public function getPartialShipment(): AbstractPartialShipment
    {
        return $this->partialShipment;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getOrderDatetime(): DateTimeImmutable
    {
        return $this->orderDatetime;
    }
}
