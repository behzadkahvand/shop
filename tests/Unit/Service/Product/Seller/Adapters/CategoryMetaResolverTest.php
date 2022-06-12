<?php

namespace App\Tests\Unit\Service\Product\Seller\Adapters;

use App\Entity\Category;
use App\Entity\Seller;
use App\Repository\SellerRepository;
use App\Service\Product\Seller\Adapters\CategoryMetaResolver;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class CategoryMetaResolverTest extends MockeryTestCase
{
    public function testResolve()
    {
        $category = new Category();
        $category->setCode('test');
        $category->setTitle('test');

        $categories = [
            $category
        ];

        $sellerRepositoryMock = \Mockery::mock(SellerRepository::class);
        $sellerRepositoryMock->shouldReceive('getSellerCategories')
            ->with(\Mockery::type(Seller::class))
            ->andReturn($categories);

        $brandMetaResolver = new CategoryMetaResolver($sellerRepositoryMock);

        $this->assertEquals(
            $brandMetaResolver->resolve(\Mockery::mock(Seller::class)),
            ['categories' => ['items' => $categories]]
        );
    }
}
