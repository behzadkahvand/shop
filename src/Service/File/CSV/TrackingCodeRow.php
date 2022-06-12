<?php

namespace App\Service\File\CSV;

use App\Service\File\RowAbstract;

final class TrackingCodeRow extends RowAbstract
{
    public string $orderShipmentId;

    public string $trackingCode;

    public string $lineNumber;

    private function __construct(
        string $lineNumber,
        string $orderShipmentId,
        string $trackingCode
    ) {
        $this->lineNumber = $lineNumber;
        $this->orderShipmentId = $orderShipmentId;
        $this->trackingCode    = $trackingCode;
    }

    public static function fromString(string $lineNumber, string $orderShipmentId, string $trackingCode): self
    {
        return new self($lineNumber, $orderShipmentId, $trackingCode);
    }
}
