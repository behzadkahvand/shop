<?php

namespace App\Service\OrderAffiliator\Normalizer;

use App\Entity\Order;

interface AffiliatorPurchaseRequestDataNormalizerInterface
{
    public function normalize(Order $order): array;
}
