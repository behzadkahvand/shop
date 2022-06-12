<?php

namespace App\Service\Order;

use App\Service\Identifier\IdentifierServiceInterface;
use Jenssegers\Optimus\Optimus;

class OrderIdentifierService implements IdentifierServiceInterface
{
    private const PRIME = 346091233;
    private const INVERSE = 8443169;
    private const RANDOM = 1776363997;

    public function generateIdentifier($order): string
    {
        $optimus = new Optimus(self::PRIME, self::INVERSE, self::RANDOM);

        return $optimus->encode($order->getId());
    }
}
