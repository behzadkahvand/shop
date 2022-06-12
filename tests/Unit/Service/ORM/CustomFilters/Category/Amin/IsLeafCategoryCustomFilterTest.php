<?php

namespace App\Tests\Unit\Service\ORM\CustomFilters\Category\Amin;

use App\Repository\CategoryRepository;
use App\Service\ORM\CustomFilters\Category\Admin\IsLeafCategoryCustomFilter;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class IsLeafCategoryCustomFilterTest
 */
final class IsLeafCategoryCustomFilterTest extends MockeryTestCase
{
    public function testItReturnIfIsLeafFilterIsNotSet()
    {
        $categoryRepository = \Mockery::mock(CategoryRepository::class);
        $categoryRepository->shouldNotReceive('getCategoryLeafIds');

        $customFilter = new IsLeafCategoryCustomFilter($categoryRepository);

        $request = new Request();

        $customFilter->apply($request);

        self::assertEquals([], $request->query->all());
    }

    public function testItApplyIsLeafFilter()
    {
        $categoryRepository = \Mockery::mock(CategoryRepository::class);
        $categoryRepository->shouldReceive('getCategoryLeafIds')->once()->withNoArgs()->andReturn([1, 2]);

        $request      = new Request(['filter' => ['isLeaf' => true]]);
        $customFilter = new IsLeafCategoryCustomFilter($categoryRepository);

        $customFilter->apply($request);

        self::assertEquals(['filter' => ['id' => ['in' => '1,2']]], $request->query->all());
    }

    public function testItApplyIsNotLeafFilter()
    {
        $categoryRepository = \Mockery::mock(CategoryRepository::class);
        $categoryRepository->shouldReceive('getCategoryLeafIds')->once()->withNoArgs()->andReturn([1, 2]);

        $request      = new Request(['filter' => ['isLeaf' => false]]);
        $customFilter = new IsLeafCategoryCustomFilter($categoryRepository);

        $customFilter->apply($request);

        self::assertEquals(['filter' => ['id' => ['nin' => '1,2']]], $request->query->all());
    }
}
