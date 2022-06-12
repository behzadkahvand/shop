<?php

namespace App\Service\Zones\ZoneDetector\Exceptions;

final class ZoneNotFoundException extends \InvalidArgumentException
{
    /**
     * ZoneNotFoundException constructor.
     */
    public function __construct()
    {
        parent::__construct('Unable to find a zone for given address.');
    }
}
