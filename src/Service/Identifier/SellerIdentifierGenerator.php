<?php

namespace App\Service\Identifier;

use App\Entity\Seller;

/**
 * Class SellerIdentifierGenerator
 */
final class SellerIdentifierGenerator extends AbstractIdentifierGenerator
{
    private const PRIME = 729161159;
    private const INVERSE = 311464951;
    private const XOR = 1108522596;

    protected function getPrime(): int
    {
        return self::PRIME;
    }

    protected function getInverse(): int
    {
        return self::INVERSE;
    }

    protected function getXor(): int
    {
        return self::XOR;
    }

    protected function getSupportedEntityType(): string
    {
        return Seller::class;
    }
}
