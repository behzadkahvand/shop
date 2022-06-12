<?php

namespace App\Dictionary;

/**
 * Class ProductSearchDataMapping
 */
final class ProductSearchDataMappingDictionary extends Dictionary
{
    public const CUSTOMER_AREA_MAPPING = [
        'filters' => [
            'price'       => 'buyBox.finalPrice',
            'brandId'     => 'brand.id',
            'brand'       => 'brand.code',
            'category'    => 'category.code',
            'title'       => 'title',
            'available'   => 'isAvailable',
            'is_campaign' => 'buyBox.hasCampaign',
            'original'    => 'isOriginal',
            'promotion'   => 'hasPromotion',
        ],
        'sorts'   => [
            'price'        => 'buyBox.finalPrice',
            'mostVisited'  => 'visits',
            'bestSellers'  => 'orderCount',
            'latest'       => 'buyBox.createdAt',
            'deliveryTime' => 'buyBox.leadTime',
            'campaign'     => 'buyBox.hasCampaign',
            'promotion'    => 'promotion',
        ],
    ];

    public const CUSTOMER_SELLER_SEARCH_AREA_MAPPING = [
        'filters' => [
            'price'     => 'productVariants.inventories.finalPrice',
            'seller'    => 'productVariants.inventories.seller.identifier',
            'brandId'   => 'brand.id',
            'brand'     => 'brand.code',
            'category'  => 'category.code',
            'title'     => 'title',
            'available' => 'isAvailable',
            'original'  => 'isOriginal',
        ],
        'sorts'   => [
            'price'        => 'productVariants.inventories.finalPrice',
            'mostVisited'  => 'visits',
            'bestSellers'  => 'orderCount',
            'latest'       => 'productVariants.inventories.createdAt',
            'deliveryTime' => 'productVariants.inventories.leadTime'
        ],
    ];

    public const SELLER_AREA_MAPPING = [
        'filters' => [
            'brandId'  => 'brand.id',
            'category' => 'category.code',
            'title'    => 'title',
            'original' => 'isOriginal',
            'status'   => 'status',
        ],
        'sorts'   => [],
    ];
}
