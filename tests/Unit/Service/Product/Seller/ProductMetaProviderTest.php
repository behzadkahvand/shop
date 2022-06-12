<?php

namespace App\Tests\Unit\Service\Product\Seller;

use App\Entity\Seller;
use App\Service\Product\Seller\Adapters\BrandMetaResolver;
use App\Service\Product\Seller\Adapters\CategoryMetaResolver;
use App\Service\Product\Seller\ProductMetaProvider;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class ProductMetaProviderTest extends MockeryTestCase
{
    public function testResolve()
    {
        $brandMetaResponse = [
            'brands' => [
                'items' => [
                    [
                        'id' => 1,
                        'title' => 'test',
                        'code' => 'test'
                    ]
                ]
            ]
        ];
        $brandMetaResolverMock = \Mockery::mock(BrandMetaResolver::class);
        $brandMetaResolverMock->shouldReceive('resolve')
            ->with(\Mockery::type(Seller::class))
            ->andReturn($brandMetaResponse);

        $categoryMetaResponse = [
            'categories' => [
                'items' => [
                    [
                        'id' => 1,
                        'title' => 'test',
                        'code' => 'test'
                    ]
                ]
            ]
        ];
        $categoryMetaResolverMock = \Mockery::mock(CategoryMetaResolver::class);
        $categoryMetaResolverMock->shouldReceive('resolve')
            ->with(\Mockery::type(Seller::class))
            ->andReturn($categoryMetaResponse);

        $resolvers = [
            $brandMetaResolverMock,
            $categoryMetaResolverMock
        ];

        $productMetaProvider = new ProductMetaProvider($resolvers);
        $result = $productMetaProvider->resolve(\Mockery::mock(Seller::class));

        $this->assertEquals($result, array_merge($categoryMetaResponse, $brandMetaResponse));
    }
}
