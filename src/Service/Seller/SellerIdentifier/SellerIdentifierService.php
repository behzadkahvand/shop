<?php

namespace App\Service\Seller\SellerIdentifier;

use Hashids\Hashids;

final class SellerIdentifierService
{
    protected const SALT = 'Pwmd6sfoPxlWDQfcGN9eC8O3vlB731hJne1WQerY';

    protected const MIN_LENGTH = 5;

    protected const ALPHABET = 'abcdefghijklmnopqrstuvwxyz1234567890';

    protected Hashids $hashIds;

    public function __construct()
    {
        $this->hashIds = new Hashids(self::SALT, self::MIN_LENGTH, self::ALPHABET);
    }

    public function generate(int $id): string
    {
        return $this->hashIds->encode($id);
    }
}
