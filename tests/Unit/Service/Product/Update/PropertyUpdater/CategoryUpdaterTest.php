<?php

namespace App\Tests\Unit\Service\Product\Update\PropertyUpdater;

use App\Entity\Category;
use App\Entity\Product;
use App\Repository\CategoryRepository;
use App\Service\Product\Update\PropertyUpdaters\CategoryUpdater;
use App\Tests\TestDoubles\Fakes\FakeDigikalaDkp;
use App\Tests\Unit\BaseUnitTestCase;
use Mockery;

class CategoryUpdaterTest extends BaseUnitTestCase
{
    public function testShouldFindAndSetCategoryOnProduct(): void
    {
        $dkp = FakeDigikalaDkp::build();

        $product = new Product();

        $category = new Category();
        $categoryRepo = Mockery::mock(CategoryRepository::class);
        $categoryRepo->expects('findLeafCategoryByTitle')->with($dkp['product']['category']['title_fa'])->andReturn($category);

        $sut = new CategoryUpdater($categoryRepo);

        $this->assertNull($product->getCategory());

        $sut->update($product, $dkp);

        $this->assertSame($category, $product->getCategory());
    }

    public function testShouldSetARandomCategoryOnProductIfCategoryDoesNotFound(): void
    {
        $dkp = FakeDigikalaDkp::build();

        $product = new Product();

        $category = new Category();
        $categoryRepo = Mockery::mock(CategoryRepository::class);
        $categoryRepo->expects('findLeafCategoryByTitle')->with($dkp['product']['category']['title_fa'])->andReturn(null);
        $categoryRepo->expects('findOneRandomLeafCategory')->withNoArgs()->andReturn($category);

        $sut = new CategoryUpdater($categoryRepo);

        $this->assertNull($product->getCategory());

        $sut->update($product, $dkp);

        $this->assertSame($category, $product->getCategory());
    }

    public function testShouldSetARandomCategoryOnProductIfCategoryNameIsNull(): void
    {
        $dkp = FakeDigikalaDkp::build();
        $dkp['product']['category']['title_fa'] = null;

        $product = new Product();

        $category = new Category();
        $categoryRepo = Mockery::mock(CategoryRepository::class);
        $categoryRepo->expects('findOneRandomLeafCategory')->withNoArgs()->andReturn($category);

        $sut = new CategoryUpdater($categoryRepo);

        $this->assertNull($product->getCategory());

        $sut->update($product, $dkp);

        $this->assertSame($category, $product->getCategory());
    }
}
