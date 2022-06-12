<?php

namespace App\Tests\Unit\Service\Product\Update\PropertyUpdater;

use App\Entity\Brand;
use App\Entity\Product;
use App\Repository\BrandRepository;
use App\Service\Product\Update\PropertyUpdaters\BrandUpdater;
use App\Tests\TestDoubles\Fakes\FakeDigikalaDkp;
use App\Tests\Unit\BaseUnitTestCase;
use Mockery;

class BrandUpdaterTest extends BaseUnitTestCase
{
    public function testShouldFindAndSetBrandOnProduct(): void
    {
        $dkp = FakeDigikalaDkp::build();

        $product = new Product();

        $brand = new Brand();
        $brandRepo = Mockery::mock(BrandRepository::class);
        $brandRepo->expects('findOneBy')->with(['title' => $dkp['product']['brand']['title_fa']])->andReturn($brand);

        $sut = new BrandUpdater($brandRepo);

        self::assertNull($product->getBrand());

        $sut->update($product, $dkp);

        self::assertSame($brand, $product->getBrand());
    }
    public function testShouldSetOthersBrandOnProductIfBrandDoesNotFoung(): void
    {
        $dkp = FakeDigikalaDkp::build();

        $product = new Product();

        $brand = new Brand();
        $brandRepo = Mockery::mock(BrandRepository::class);
        $brandRepo->expects('findOneBy')->with(['title' => $dkp['product']['brand']['title_fa']])->andReturn(null);
        $brandRepo->expects('findOneBy')->with(['title' => 'متفرقه'])->andReturn($brand);

        $sut = new BrandUpdater($brandRepo);

        self::assertNull($product->getBrand());

        $sut->update($product, $dkp);

        self::assertSame($brand, $product->getBrand());
    }
}
