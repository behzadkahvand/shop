<?php

namespace App\Tests\Unit\Service\Product\Search\Listeners\Doctrine;

use App\Entity\Category;
use App\Events\Product\Search\ProductSearchDataEvent;
use App\Events\Product\Search\SellerProductSearchDataEvent;
use App\Repository\CategoryRepository;
use App\Service\Product\Search\DoctrineSearchData;
use App\Service\Product\Search\Drivers\DoctrineProductSearchDriver;
use App\Service\Product\Search\Exceptions\CategoryNotFoundException;
use App\Service\Product\Search\Listeners\Doctrine\CategoryCodeEventListener;
use App\Service\Utils\Pagination\Pagination;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class CategoryCodeEventListenerTest extends MockeryTestCase
{
    /**
     * @var CategoryRepository|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $categoryRepoMock;

    /**
     * @var Category|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $categoryMock;

    protected CategoryCodeEventListener $categoryCodeEventListener;

    protected function setUp(): void
    {
        parent::setUp();

        $this->categoryRepoMock = Mockery::mock(CategoryRepository::class);
        $this->categoryMock = Mockery::mock(Category::class);

        $this->categoryCodeEventListener = new CategoryCodeEventListener($this->categoryRepoMock);
    }

    public function testItCanGetSubscribedEvents(): void
    {
        $result = $this->categoryCodeEventListener::getSubscribedEvents();

        self::assertEquals([
            ProductSearchDataEvent::class       => ['onProductSearchDataEvent', 0],
            SellerProductSearchDataEvent::class => ['onProductSearchDataEvent', 0],
        ], $result);
    }

    public function testItDoNothingWhenDriverIsInvalid(): void
    {
        $event = new ProductSearchDataEvent(
            'invalid',
            new DoctrineSearchData(['category.code' => 'category_code'], []),
            new Pagination()
        );

        self::assertNull($this->categoryCodeEventListener->onProductSearchDataEvent($event));

        $data = $event->getData();

        self::assertEquals(['category.code' => 'category_code'], $data->getFilters());
        self::assertEquals([], $data->getSorts());
    }

    public function testItDoNothingWhenCategoryCodeFilterIsNotExists(): void
    {
        $event = new ProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new DoctrineSearchData([], []),
            new Pagination()
        );

        self::assertNull($this->categoryCodeEventListener->onProductSearchDataEvent($event));

        $data = $event->getData();

        self::assertEquals([], $data->getFilters());
        self::assertEquals([], $data->getSorts());
    }

    public function testItDoNothingWhenCategoryIsNotFound(): void
    {
        $this->categoryRepoMock->shouldReceive('findOneBy')
            ->once()
            ->with(['code' => 'category_code'])
            ->andReturnNull();

        $event = new ProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new DoctrineSearchData(['category.code' => 'category_code'], []),
            new Pagination()
        );

        $this->expectException(CategoryNotFoundException::class);
        $this->expectExceptionCode(404);
        $this->expectExceptionMessage('Selected category not found!');

        $this->categoryCodeEventListener->onProductSearchDataEvent($event);
    }

    public function testItCanSetCategoryIdFilter(): void
    {
        $this->categoryRepoMock->shouldReceive('findOneBy')
            ->once()
            ->with(['code' => 'category_code'])
            ->andReturn($this->categoryMock);

        $this->categoryRepoMock->shouldReceive('getCategoryLeafIdsForCategory')
            ->once()
            ->with($this->categoryMock)
            ->andReturn('1,2');

        $event = new ProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new DoctrineSearchData(['category.code' => 'category_code'], [], null, 'title'),
            new Pagination()
        );

        $this->categoryCodeEventListener->onProductSearchDataEvent($event);

        $data = $event->getData();

        self::assertEquals(['category.id' => ['in' => '1,2']], $data->getFilters());
        self::assertEquals([], $data->getSorts());
        self::assertEquals('category_code', $data->getCategoryCode());
        self::assertEquals('title', $data->getTitle());
    }
}
