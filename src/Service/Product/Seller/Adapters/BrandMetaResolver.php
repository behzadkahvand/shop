<?php

namespace App\Service\Product\Seller\Adapters;

use App\Entity\Seller;
use App\Repository\SellerRepository;

class BrandMetaResolver implements MetaResolverInterface
{
    private SellerRepository $sellerRepository;

    public function __construct(SellerRepository $sellerRepository)
    {
        $this->sellerRepository = $sellerRepository;
    }

    public function resolve(Seller $seller): array
    {
        return [
            'brands' => [
                'items' => $this->sellerRepository->getSellerBrands($seller)
            ]
        ];
    }
}
