<?php

namespace App\Tests\Unit\Service\Layout\Block;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Service\Layout\Block\PromotionsBlock;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use stdClass;

class PromotionsBlockTest extends MockeryTestCase
{
    private $productRepo;

    private $categoryRepo;

    private $em;

    public function testItCanGenerateForAllCategories(): void
    {
        $productRepo  = Mockery::mock(ProductRepository::class);
        $categoryRepo = Mockery::mock(CategoryRepository::class);
        $builder      = Mockery::mock(QueryBuilder::class);
        $builder->shouldReceive('getQuery')->once()->withNoArgs()->andReturnSelf();
        $builder->shouldReceive('setHint')->once()->with(Query::HINT_REFRESH, true)->andReturnSelf();
        $builder->shouldReceive('getResult')
                ->once()
                ->withNoArgs()
                ->andReturn([new stdClass(), new stdClass()], [new stdClass(), new stdClass()]);

        $categoryRepo->shouldNotReceive('getCategoryLeafIds');

        $productRepo
            ->shouldReceive('listByCategoriesWithPromotion')
            ->once()
            ->with([], 10, true)
            ->andReturn($builder);


        $this->em
            ->shouldReceive('clear')
            ->once()
            ->withNoArgs()
            ->andReturn();

        $service = new PromotionsBlock($productRepo, $categoryRepo, $this->em);

        self::assertEquals([
            'all' => [new stdClass(), new stdClass()],
        ], $service->generate(['promotions' => ['all']]));
    }

    public function testItCanGenerateWithCategoriesProvided(): void
    {
        $category1 = Mockery::mock(Category::class);
        $category2 = Mockery::mock(Category::class);
        $builder   = Mockery::mock(QueryBuilder::class);
        $builder->shouldReceive('getQuery')->twice()->withNoArgs()->andReturnSelf();
        $builder->shouldReceive('setHint')->twice()->with(Query::HINT_REFRESH, true)->andReturnSelf();
        $builder->shouldReceive('getResult')
                ->twice()
                ->withNoArgs()
                ->andReturn([new stdClass(), new stdClass()], [new stdClass(), new stdClass()]);

        $category1->shouldReceive('getCode')->once()->withNoArgs()->andReturn('mobile');
        $category2->shouldReceive('getCode')->once()->withNoArgs()->andReturn('desktop');

        $this->categoryRepo
            ->shouldReceive('getCategoryLeafIdsForCategory')
            ->once()
            ->with($category1)
            ->andReturn('1,3');
        $this->categoryRepo
            ->shouldReceive('getCategoryLeafIdsForCategory')
            ->once()
            ->with($category2)
            ->andReturn('2,4');
        $this->categoryRepo
            ->shouldReceive('getReferenceByCodes')
            ->once()
            ->with(['mobile', 'desktop'])
            ->andReturn([$category1, $category2]);

        $this->productRepo
            ->shouldReceive('listByCategoriesWithPromotion')
            ->once()
            ->with([1, 3], 10, true)
            ->andReturn($builder);

        $this->productRepo
            ->shouldReceive('listByCategoriesWithPromotion')
            ->once()
            ->with([2, 4], 10, true)
            ->andReturn($builder);

        $this->em
            ->shouldReceive('clear')
            ->twice()
            ->withNoArgs()
            ->andReturn();

        $service = new PromotionsBlock($this->productRepo, $this->categoryRepo, $this->em);

        self::assertEquals([
            'mobile'  => [new stdClass(), new stdClass()],
            'desktop' => [new stdClass(), new stdClass()],
        ], $service->generate(['promotions' => ['mobile', 'desktop', 'mobile']]));
    }

    public function testItCanGenerateWithCategoriesNotProvided(): void
    {
        $this->productRepo->shouldNotReceive('listByCategoriesWithPromotion');

        $this->categoryRepo->shouldNotReceive('findBy');

        $service = new PromotionsBlock($this->productRepo, $this->categoryRepo, $this->em);
        self::assertEquals([], $service->generate([]));
    }

    public function testItGetCacheSignature()
    {
        $service = new PromotionsBlock($this->productRepo, $this->categoryRepo, $this->em);

        self::assertEquals(
            'mobile_desktop',
            $service->getCacheSignature(['promotions' => ['mobile', 'desktop', 'mobile']])
        );
    }

    public function testItGetCode(): void
    {
        $service = new PromotionsBlock($this->productRepo, $this->categoryRepo, $this->em);

        self::assertEquals('promotions', $service->getCode());
    }

    public function testItGetCacheExpiry(): void
    {
        $service = new PromotionsBlock($this->productRepo, $this->categoryRepo, $this->em);

        self::assertEquals(360, $service->getCacheExpiry());
    }

    protected function mockeryTestSetUp(): void
    {
        $this->productRepo  = \Mockery::mock(ProductRepository::class);
        $this->categoryRepo = \Mockery::mock(CategoryRepository::class);
        $this->em           = \Mockery::mock(EntityManagerInterface::class);
    }

    protected function mockeryTestTearDown()
    {
        unset($this->productRepo, $this->categoryRepo, $this->em);
        \Mockery::close();
    }
}
