<?php

namespace App\Tests\Unit\Service\ORM\CustomFilters\Product\Admin;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Service\ORM\CustomFilters\Product\Admin\CategoryProductsCustomFilter;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CategoryProductsCustomFilterTest
 */
final class CategoryProductsCustomFilterTest extends MockeryTestCase
{
    public function testItReturnIfCategoryFilterIsNotSet(): void
    {
        $categoryRepository = \Mockery::mock(CategoryRepository::class);
        $customFilter       = new CategoryProductsCustomFilter($categoryRepository);

        $request = new Request();
        $customFilter->apply($request);

        self::assertEquals([], $request->query->all());
    }

    public function testItDontShowProductsOfCategoryWithInvalidId(): void
    {
        $invalidCategoryId = -1000;

        $categoryRepository = \Mockery::mock(CategoryRepository::class);
        $categoryRepository->shouldReceive('find')->once()->with($invalidCategoryId)->andReturnNull();

        $customFilter = new CategoryProductsCustomFilter($categoryRepository);

        $request = new Request([
            'filter' => [
                'category' => $invalidCategoryId,
            ],
        ]);

        $customFilter->apply($request);

        self::assertEquals(['filter' => ['category.id' => ['in' => '-1']]], $request->query->all());
    }

    public function testItApplyCategoryProductsFilter(): void
    {
        $categoryId = 1;
        $category   = \Mockery::mock(Category::class);
        $category->shouldReceive('isLeaf')->once()->withNoArgs()->andReturnFalse();

        $categoryRepository = \Mockery::mock(CategoryRepository::class);
        $categoryRepository->shouldReceive('find')->once()->with($categoryId)->andReturn($category);
        $categoryRepository->shouldReceive('getCategoryLeafIdsForCategory')
                           ->once()
                           ->with($category)
                           ->andReturn('2,3,4');

        $customFilter = new CategoryProductsCustomFilter($categoryRepository);

        $request = new Request([
            'filter' => [
                'category' => $categoryId,
            ],
        ]);

        $customFilter->apply($request);

        self::assertEquals(['filter' => ['category.id' => ['in' => '2,3,4']]], $request->query->all());
    }

    public function testItApplyCategoryProductsFilterForLeafCategories(): void
    {
        $categoryId = 1;
        $category   = \Mockery::mock(Category::class);
        $category->shouldReceive('isLeaf')->once()->withNoArgs()->andReturnTrue();

        $categoryRepository = \Mockery::mock(CategoryRepository::class);
        $categoryRepository->shouldReceive('find')->once()->with($categoryId)->andReturn($category);

        $customFilter = new CategoryProductsCustomFilter($categoryRepository);

        $request = new Request([
            'filter' => [
                'category' => $categoryId,
            ],
        ]);

        $customFilter->apply($request);

        self::assertEquals(['filter' => ['category.id' => ['in' => '1']]], $request->query->all());
    }

    public function testItNormalizeGivenFilter(): void
    {
        $categoryId = 1;
        $category   = \Mockery::mock(Category::class);
        $category->shouldReceive('isLeaf')->once()->withNoArgs()->andReturnFalse();

        $categoryRepository = \Mockery::mock(CategoryRepository::class);
        $categoryRepository->shouldReceive('find')->once()->with($categoryId)->andReturn($category);
        $categoryRepository->shouldReceive('getCategoryLeafIdsForCategory')
                           ->once()
                           ->with($category)
                           ->andReturn('2,3,4');

        $customFilter = new CategoryProductsCustomFilter($categoryRepository);

        $request = new Request([
            'filter' => [
                'category' => ['int' => (string) $categoryId],
            ],
        ]);

        $customFilter->apply($request);

        self::assertEquals(['filter' => ['category.id' => ['in' => '2,3,4']]], $request->query->all());
    }
}
