<?php

namespace App\Messaging\Messages\Command\Log;

use App\Entity\SellerScore;

class SellerScoreUpdateMessage
{
    public function __construct(protected int $sellerId, protected SellerScore $sellerScore)
    {
    }

    public function getSellerScore(): SellerScore
    {
        return $this->sellerScore;
    }

    public function getSellerId(): int
    {
        return $this->sellerId;
    }
}
