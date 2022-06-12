<?php

namespace App\Service\PartialShipment\Exceptions;

/**
 * Class PartialShipmentCanNotBeFreezedException
 */
final class PartialShipmentCanNotBeFreezedException extends \RuntimeException
{
    /**
     * PartialShipmentCanNotBeFreezedException constructor.
     *
     * @param string $message
     */
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
