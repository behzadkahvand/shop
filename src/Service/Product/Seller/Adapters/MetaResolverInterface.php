<?php

namespace App\Service\Product\Seller\Adapters;

use App\Entity\Seller;

interface MetaResolverInterface
{
    public function resolve(Seller $seller): array;
}
