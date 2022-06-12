<?php

namespace App\Service\Product\Seller\Adapters;

use App\Entity\Seller;
use App\Repository\SellerRepository;

class CategoryMetaResolver implements MetaResolverInterface
{
    private SellerRepository $sellerRepository;

    public function __construct(SellerRepository $sellerRepository)
    {
        $this->sellerRepository = $sellerRepository;
    }

    public function resolve(Seller $seller): array
    {
        return [
            'categories' => [
                'items' => $this->sellerRepository->getSellerCategories($seller)
            ],
        ];
    }
}
