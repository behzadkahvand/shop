<?php

namespace App\Tests\Unit\Service\Product\Seller\Adapters;

use App\Entity\Brand;
use App\Entity\Seller;
use App\Repository\SellerRepository;
use App\Service\Product\Seller\Adapters\BrandMetaResolver;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class BrandMetaResolverTest extends MockeryTestCase
{
    public function testResolve()
    {
        $brand = new Brand();
        $brand->setCode('test');
        $brand->setTitle('test');

        $brands = [
            $brand
        ];

        $sellerRepositoryMock = \Mockery::mock(SellerRepository::class);
        $sellerRepositoryMock->shouldReceive('getSellerBrands')
            ->with(\Mockery::type(Seller::class))
            ->andReturn($brands);

        $brandMetaResolver = new BrandMetaResolver($sellerRepositoryMock);

        $this->assertEquals(
            $brandMetaResolver->resolve(\Mockery::mock(Seller::class)),
            ['brands' => ['items' => $brands]]
        );
    }
}
