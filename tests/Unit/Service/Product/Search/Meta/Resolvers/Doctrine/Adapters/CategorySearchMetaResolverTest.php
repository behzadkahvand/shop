<?php

namespace App\Tests\Unit\Service\Product\Search\Meta\Resolvers\Doctrine\Adapters;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Service\Product\Search\DoctrineSearchData;
use App\Service\Product\Search\Meta\Resolvers\Doctrine\Adapters\CategorySearchMetaResolver;
use App\Service\Product\Search\SearchData;
use App\Service\Product\Search\Utils\Seo\SeoMetaDataCalculator;
use App\Service\Product\Search\Utils\Seo\ValueObjects\SeoMetaDataValueObject;
use App\Service\Utils\Pagination\Pagination;
use Doctrine\ORM\QueryBuilder;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class CategorySearchMetaResolverTest extends MockeryTestCase
{
    /**
     * @var CategoryRepository|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $categoryRepoMock;

    /**
     * @var SeoMetaDataCalculator|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $seoMetaDataCalcMock;

    /**
     * @var Category|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $categoryMock;

    /**
     * @var QueryBuilder|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $queryBuilderMock;

    protected SearchData $data;

    protected Pagination $pagination;

    protected SeoMetaDataValueObject $seoMetaData;

    protected CategorySearchMetaResolver $categorySearchMetaResolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->categoryRepoMock    = Mockery::mock(CategoryRepository::class);
        $this->seoMetaDataCalcMock = Mockery::mock(SeoMetaDataCalculator::class);
        $this->categoryMock        = Mockery::mock(Category::class);
        $this->queryBuilderMock    = Mockery::mock(QueryBuilder::class);

        $this->pagination  = new Pagination();
        $this->seoMetaData = new SeoMetaDataValueObject();

        $this->categorySearchMetaResolver = new CategorySearchMetaResolver(
            $this->categoryRepoMock,
            $this->seoMetaDataCalcMock
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->pagination,
            $this->seoMetaData,
            $this->categorySearchMetaResolver
        );

        $this->categoryRepoMock    = null;
        $this->seoMetaDataCalcMock = null;
        $this->categoryMock        = null;
        $this->queryBuilderMock    = null;
    }

    public function testItCanNotResolveCategorySearchMetaData(): void
    {
        $this->data = new SearchData([], []);

        $result = $this->categorySearchMetaResolver->resolve($this->queryBuilderMock, $this->data, $this->pagination);

        self::assertEquals([], $result);
    }

    public function testItCanResolveCategorySearchMetaDataWhenCategoryExistsAndCategoryIsNotLeaf(): void
    {
        $this->data = new DoctrineSearchData(['category.code' => 'category_code'], [], 'category_code');

        $this->seoMetaData->setCategory($this->categoryMock);

        $this->seoMetaDataCalcMock->shouldReceive('getData')
                                  ->once()
                                  ->with($this->data)
                                  ->andReturn($this->seoMetaData);

        $this->categoryRepoMock->shouldReceive('getPath')
                               ->once()
                               ->with($this->categoryMock)
                               ->andReturn([
                                   $this->categoryMock,
                               ]);

        $this->categoryMock->shouldReceive('getId')
                           ->times(8)
                           ->withNoArgs()
                           ->andReturn(1, 1, 2, 3, 4, 2, 3, 4);
        $this->categoryMock->shouldReceive('getCode')
                           ->times(5)
                           ->withNoArgs()
                           ->andReturn('code_1', 'code_1', 'code_2', 'code_3', 'code_4');
        $this->categoryMock->shouldReceive('getTitle')
                           ->times(5)
                           ->withNoArgs()
                           ->andReturn('title_1', 'title_1', 'title_2', 'title_3', 'title_4');
        $this->categoryMock->shouldReceive('getLevel')
                           ->times(17)
                           ->withNoArgs()
                           ->andReturn(1, 2, 2, 2, 2, 2, 1, 2, 1, 2, 2, 2, 2, 2, 2, 2, 2);

        $this->categoryMock->shouldReceive('isLeaf')
                           ->once()
                           ->withNoArgs()
                           ->andReturnFalse();

        $this->categoryRepoMock->shouldReceive('getChildren')
                               ->once()
                               ->with($this->categoryMock, true, null, 'ASC', true)
                               ->andReturn([
                                   $this->categoryMock,
                                   $this->categoryMock,
                                   $this->categoryMock,
                                   $this->categoryMock,
                               ]);

        $result = $this->categorySearchMetaResolver->resolve($this->queryBuilderMock, $this->data, $this->pagination);

        self::assertCount(1, $result);
        self::assertArrayHasKey('categories', $result);

        $categoriesMetas = $result['categories'];
        self::assertArrayHasKey('pageTitle', $categoriesMetas);
        self::assertArrayHasKey('breadcrumb', $categoriesMetas);
        self::assertArrayHasKey('hierarchy', $categoriesMetas);

        $hierarchies = [
            [
                'id'          => 1,
                'code'        => 'code_1',
                'title'       => 'title_1',
                'pageTitle'   => 'title_1',
                'level'       => 1,
                'hasChildren' => true,
            ],
            [
                'id'          => 2,
                'code'        => 'code_2',
                'title'       => 'title_2',
                'pageTitle'   => 'title_2',
                'level'       => 2,
                'hasChildren' => false,
            ],
            [
                'id'          => 3,
                'code'        => 'code_3',
                'title'       => 'title_3',
                'pageTitle'   => 'title_3',
                'level'       => 2,
                'hasChildren' => false,
            ],
            [
                'id'          => 4,
                'code'        => 'code_4',
                'title'       => 'title_4',
                'pageTitle'   => 'title_4',
                'level'       => 2,
                'hasChildren' => false,
            ],
        ];

        self::assertCount(1, $categoriesMetas['hierarchy']);

        foreach ($categoriesMetas['hierarchy'] as $key => $hierarchy) {
            $categoryHierarchy = $hierarchy->jsonSerialize();

            self::assertArrayHasKey('id', $categoryHierarchy);
            self::assertArrayHasKey('code', $categoryHierarchy);
            self::assertArrayHasKey('title', $categoryHierarchy);
            self::assertArrayHasKey('level', $categoryHierarchy);
            self::assertArrayHasKey('hasChildren', $categoryHierarchy);
            self::assertArrayHasKey('children', $categoryHierarchy);

            self::assertEquals($hierarchies[$key]['id'], $categoryHierarchy['id']);
            self::assertEquals($hierarchies[$key]['code'], $categoryHierarchy['code']);
            self::assertEquals($hierarchies[$key]['title'], $categoryHierarchy['title']);
            self::assertEquals($hierarchies[$key]['level'], $categoryHierarchy['level']);
            self::assertTrue($categoryHierarchy['hasChildren']);

            self::assertCount(3, $categoryHierarchy['children']);

            foreach ($categoryHierarchy['children'] as $keyChild => $hierarchyChild) {
                $hierarchyChild = $hierarchyChild->jsonSerialize();

                self::assertArrayHasKey('id', $hierarchyChild);
                self::assertArrayHasKey('code', $hierarchyChild);
                self::assertArrayHasKey('title', $hierarchyChild);
                self::assertArrayHasKey('level', $hierarchyChild);
                self::assertArrayHasKey('hasChildren', $hierarchyChild);
                self::assertArrayHasKey('children', $hierarchyChild);

                self::assertEquals($hierarchies[$keyChild + 1]['id'], $hierarchyChild['id']);
                self::assertEquals($hierarchies[$keyChild + 1]['code'], $hierarchyChild['code']);
                self::assertEquals($hierarchies[$keyChild + 1]['title'], $hierarchyChild['title']);
                self::assertEquals($hierarchies[$keyChild + 1]['level'], $hierarchyChild['level']);
                self::assertFalse($hierarchyChild['hasChildren']);

                self::assertEquals([], $hierarchyChild['children']);
            }
        }

        self::assertCount(1, $categoriesMetas['breadcrumb']);

        foreach ($categoriesMetas['breadcrumb'] as $key => $breadcrumb) {
            self::assertArrayHasKey('id', $breadcrumb);
            self::assertArrayHasKey('code', $breadcrumb);
            self::assertArrayHasKey('title', $breadcrumb);

            self::assertEquals($hierarchies[$key]['id'], $breadcrumb['id']);
            self::assertEquals($hierarchies[$key]['code'], $breadcrumb['code']);
            self::assertEquals($hierarchies[$key]['title'], $breadcrumb['title']);
        }
    }

    public function testItCanResolveCategorySearchMetaDataWhenCategoryExistsAndCategoryIsLeaf(): void
    {
        $this->data = new DoctrineSearchData(['category.code' => 'category_code'], [], 'category_code');

        $this->seoMetaData->setCategory($this->categoryMock);

        $this->seoMetaDataCalcMock->shouldReceive('getData')
                                  ->once()
                                  ->with($this->data)
                                  ->andReturn($this->seoMetaData);

        $this->categoryRepoMock->shouldReceive('getPath')
                               ->once()
                               ->with($this->categoryMock)
                               ->andReturn([
                                   $this->categoryMock,
                                   $this->categoryMock,
                                   $this->categoryMock,
                               ]);

        $this->categoryMock->shouldReceive('getId')
                           ->times(12)
                           ->withNoArgs()
                           ->andReturn(1, 2, 3, 1, 2, 3, 4, 3, 4, 2, 3, 4);
        $this->categoryMock->shouldReceive('getCode')
                           ->times(7)
                           ->withNoArgs()
                           ->andReturn('code_1', 'code_2', 'code_3', 'code_1', 'code_2', 'code_3', 'code_4');
        $this->categoryMock->shouldReceive('getTitle')
                           ->times(7)
                           ->withNoArgs()
                           ->andReturn('title_1', 'title_2', 'title_3', 'title_1', 'title_2', 'title_3', 'title_4');
        $this->categoryMock->shouldReceive('getLevel')
                           ->times(20)
                           ->withNoArgs()
                           ->andReturn(1, 2, 2, 3, 3, 3, 1, 2, 1, 2, 3, 3, 2, 3, 2, 3, 3, 3, 3, 3);

        $this->categoryMock->shouldReceive('isLeaf')
                           ->once()
                           ->withNoArgs()
                           ->andReturnTrue();

        $this->categoryRepoMock->shouldReceive('getCategorySiblings')
                               ->once()
                               ->with($this->categoryMock)
                               ->andReturn([
                                   $this->categoryMock,
                               ]);

        $result = $this->categorySearchMetaResolver->resolve($this->queryBuilderMock, $this->data, $this->pagination);

        self::assertCount(1, $result);
        self::assertArrayHasKey('categories', $result);

        $categoriesMetas = $result['categories'];
        self::assertArrayHasKey('pageTitle', $categoriesMetas);
        self::assertArrayHasKey('breadcrumb', $categoriesMetas);
        self::assertArrayHasKey('hierarchy', $categoriesMetas);

        $hierarchies = [
            [
                'id'          => 1,
                'code'        => 'code_1',
                'title'       => 'title_1',
                'pageTitle'   => 'title_1',
                'level'       => 1,
                'hasChildren' => true,
            ],
            [
                'id'          => 2,
                'code'        => 'code_2',
                'title'       => 'title_2',
                'pageTitle'   => 'title_2',
                'level'       => 2,
                'hasChildren' => true,
            ],
            [
                'id'          => 3,
                'code'        => 'code_3',
                'title'       => 'title_3',
                'pageTitle'   => 'title_3',
                'level'       => 3,
                'hasChildren' => false,
            ],
            [
                'id'          => 4,
                'code'        => 'code_4',
                'title'       => 'title_4',
                'pageTitle'   => 'title_4',
                'level'       => 3,
                'hasChildren' => false,
            ],
        ];

        self::assertCount(1, $categoriesMetas['hierarchy']);

        foreach ($categoriesMetas['hierarchy'] as $key => $hierarchy) {
            $categoryHierarchy = $hierarchy->jsonSerialize();

            self::assertArrayHasKey('id', $categoryHierarchy);
            self::assertArrayHasKey('code', $categoryHierarchy);
            self::assertArrayHasKey('title', $categoryHierarchy);
            self::assertArrayHasKey('level', $categoryHierarchy);
            self::assertArrayHasKey('hasChildren', $categoryHierarchy);
            self::assertArrayHasKey('children', $categoryHierarchy);

            self::assertEquals($hierarchies[$key]['id'], $categoryHierarchy['id']);
            self::assertEquals($hierarchies[$key]['code'], $categoryHierarchy['code']);
            self::assertEquals($hierarchies[$key]['title'], $categoryHierarchy['title']);
            self::assertEquals($hierarchies[$key]['level'], $categoryHierarchy['level']);
            self::assertTrue($categoryHierarchy['hasChildren']);

            self::assertCount(1, $categoryHierarchy['children']);

            foreach ($categoryHierarchy['children'] as $keyChild => $hierarchyChild) {
                $hierarchyChild = $hierarchyChild->jsonSerialize();

                self::assertArrayHasKey('id', $hierarchyChild);
                self::assertArrayHasKey('code', $hierarchyChild);
                self::assertArrayHasKey('title', $hierarchyChild);
                self::assertArrayHasKey('level', $hierarchyChild);
                self::assertArrayHasKey('hasChildren', $hierarchyChild);
                self::assertArrayHasKey('children', $hierarchyChild);

                self::assertEquals($hierarchies[$keyChild + 1]['id'], $hierarchyChild['id']);
                self::assertEquals($hierarchies[$keyChild + 1]['code'], $hierarchyChild['code']);
                self::assertEquals($hierarchies[$keyChild + 1]['title'], $hierarchyChild['title']);
                self::assertEquals($hierarchies[$keyChild + 1]['level'], $hierarchyChild['level']);
                self::assertTrue($hierarchyChild['hasChildren']);

                self::assertCount(2, $hierarchyChild['children']);

                foreach ($hierarchyChild['children'] as $keyChildChild => $hierarchyChildChild) {
                    $hierarchyChildChild = $hierarchyChildChild->jsonSerialize();

                    self::assertArrayHasKey('id', $hierarchyChildChild);
                    self::assertArrayHasKey('code', $hierarchyChildChild);
                    self::assertArrayHasKey('title', $hierarchyChildChild);
                    self::assertArrayHasKey('level', $hierarchyChildChild);
                    self::assertArrayHasKey('hasChildren', $hierarchyChildChild);
                    self::assertArrayHasKey('children', $hierarchyChildChild);

                    self::assertEquals($hierarchies[$keyChildChild + 2]['id'], $hierarchyChildChild['id']);
                    self::assertEquals($hierarchies[$keyChildChild + 2]['code'], $hierarchyChildChild['code']);
                    self::assertEquals($hierarchies[$keyChildChild + 2]['title'], $hierarchyChildChild['title']);
                    self::assertEquals($hierarchies[$keyChildChild + 2]['level'], $hierarchyChildChild['level']);
                    self::assertFalse($hierarchyChildChild['hasChildren']);
                }
            }
        }
    }

    public function testItCanResolveCategorySearchMetaDataWhenCategoryDoesNotExist(): void
    {
        $this->data = new DoctrineSearchData(['category.code' => 'category_code'], [], 'category_code');

        $this->seoMetaDataCalcMock->shouldReceive('getData')
                                  ->once()
                                  ->with($this->data)
                                  ->andReturn($this->seoMetaData);

        $this->categoryMock->shouldReceive('getId')
                           ->times(3)
                           ->withNoArgs()
                           ->andReturn(1, 2, 3);
        $this->categoryMock->shouldReceive('getCode')
                           ->times(3)
                           ->withNoArgs()
                           ->andReturn('code_1', 'code_2', 'code_3');
        $this->categoryMock->shouldReceive('getTitle')
                           ->times(3)
                           ->withNoArgs()
                           ->andReturn('title_1', 'title_2', 'title_3');
        $this->categoryMock->shouldReceive('getLevel')
                           ->times(5)
                           ->withNoArgs()
                           ->andReturn(1);

        $this->categoryRepoMock->shouldReceive('getRootCategories')
                               ->once()
                               ->withNoArgs()
                               ->andReturn([
                                   $this->categoryMock,
                                   $this->categoryMock,
                                   $this->categoryMock,
                               ]);

        $result = $this->categorySearchMetaResolver->resolve($this->queryBuilderMock, $this->data, $this->pagination);

        self::assertCount(1, $result);
        self::assertArrayHasKey('categories', $result);

        $categoriesMetas = $result['categories'];
        self::assertArrayHasKey('pageTitle', $categoriesMetas);
        self::assertArrayHasKey('breadcrumb', $categoriesMetas);
        self::assertArrayHasKey('hierarchy', $categoriesMetas);

        self::assertNull($categoriesMetas['pageTitle']);

        $hierarchies = [
            [
                'id'        => 1,
                'code'      => 'code_1',
                'title'     => 'title_1',
                'pageTitle' => 'title_1',
                'level'     => 1,
            ],
            [
                'id'        => 2,
                'code'      => 'code_2',
                'title'     => 'title_2',
                'pageTitle' => 'title_2',
                'level'     => 1,
            ],
            [
                'id'        => 3,
                'code'      => 'code_3',
                'title'     => 'title_3',
                'pageTitle' => 'title_3',
                'level'     => 1,
            ],
        ];

        self::assertCount(3, $categoriesMetas['hierarchy']);

        foreach ($categoriesMetas['hierarchy'] as $key => $hierarchy) {
            $categoryHierarchy = $hierarchy->jsonSerialize();

            self::assertArrayHasKey('id', $categoryHierarchy);
            self::assertArrayHasKey('code', $categoryHierarchy);
            self::assertArrayHasKey('title', $categoryHierarchy);
            self::assertArrayHasKey('level', $categoryHierarchy);
            self::assertArrayHasKey('hasChildren', $categoryHierarchy);
            self::assertArrayHasKey('children', $categoryHierarchy);

            self::assertEquals($hierarchies[$key]['id'], $categoryHierarchy['id']);
            self::assertEquals($hierarchies[$key]['code'], $categoryHierarchy['code']);
            self::assertEquals($hierarchies[$key]['title'], $categoryHierarchy['title']);
            self::assertEquals($hierarchies[$key]['level'], $categoryHierarchy['level']);
            self::assertFalse($categoryHierarchy['hasChildren']);
            self::assertEquals([], $categoryHierarchy['children']);
        }

        self::assertCount(0, $categoriesMetas['breadcrumb']);
        self::assertEquals([], $categoriesMetas['breadcrumb']);
    }
}
