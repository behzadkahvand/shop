<?php

namespace App\Tests\Unit\Service\Seo\SeoSelectedFilter;

use App\DTO\Admin\Seo\SeoSelectedFilterData;
use App\Entity\Brand;
use App\Entity\Category;
use App\Entity\Seo\SeoSelectedBrandFilter;
use App\Entity\Seo\SeoSelectedFilter;
use App\Repository\Seo\SeoSelectedBrandFilterRepository;
use App\Service\Seo\SeoSelectedFilter\AddSeoSelectedFilterService;
use App\Service\Seo\SeoSelectedFilter\UpdateOrCreateSeoSelectedFiltersService;
use App\Service\Utils\GenerateSoeMetadata;
use App\Tests\Unit\BaseUnitTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Faker\Generator;
use Mockery;

class UpdateOrCreateSeoSelectedFiltersServiceTest extends BaseUnitTestCase
{
    private ?UpdateOrCreateSeoSelectedFiltersService $updateOrCreateSeoSelectedFiltersService;
    private Mockery\LegacyMockInterface|EntityManagerInterface|Mockery\MockInterface|null $entityManagerMock;
    private Mockery\LegacyMockInterface|Mockery\MockInterface|SeoSelectedBrandFilterRepository|null $seoSelectedBrandFilterRepositoryMock;
    private GenerateSoeMetadata|Mockery\LegacyMockInterface|Mockery\MockInterface|null $generateSoeMetadataMock;
    private Mockery\LegacyMockInterface|Mockery\MockInterface|AddSeoSelectedFilterService|null $addSeoSelectedFilterServiceMock;
    private Category|Mockery\LegacyMockInterface|Mockery\MockInterface|null $categoryMock;
    private Brand|Mockery\LegacyMockInterface|Mockery\MockInterface|null $brandMock;
    private Mockery\LegacyMockInterface|SeoSelectedBrandFilter|Mockery\MockInterface|null $seoSelectedBrandFilterMock;
    private ?Generator $faker;
    private ?string $categoryName;
    /**
     * @var array[]|null
     */
    private ?array $brands;
    private ?int $categoryId;
    private ?string $brandName;
    private ?int $brandId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->entityManagerMock = Mockery::mock(EntityManagerInterface::class);
        $this->seoSelectedBrandFilterRepositoryMock = Mockery::mock(SeoSelectedBrandFilterRepository::class);
        $this->generateSoeMetadataMock = Mockery::mock(GenerateSoeMetadata::class);
        $this->addSeoSelectedFilterServiceMock = Mockery::mock(AddSeoSelectedFilterService::class);
        $this->categoryMock = Mockery::mock(Category::class);
        $this->brandMock = Mockery::mock(Brand::class);
        $this->seoSelectedBrandFilterMock = Mockery::mock(SeoSelectedBrandFilter::class);

        $this->faker = Factory::create();
        $this->categoryName = $this->faker->title();
        $this->brandName = $this->faker->title();
        $this->categoryId = $this->faker->numberBetween(1, 5);
        $this->brandId = $this->faker->numberBetween(10, 19);
        $this->brands = [
            [
                'id' => $this->brandId,
                'title' => $this->brandName
            ]
        ];

        $this->updateOrCreateSeoSelectedFiltersService = new UpdateOrCreateSeoSelectedFiltersService(
            $this->entityManagerMock,
            $this->seoSelectedBrandFilterRepositoryMock,
            $this->generateSoeMetadataMock,
            $this->addSeoSelectedFilterServiceMock
        );
    }

    public function testItCanCreateWhenNotExistSeoFilterBrand(): void
    {
        $this->categoryMock->expects('getId')
            ->with()
            ->andReturns($this->categoryId);

        $this->seoSelectedBrandFilterRepositoryMock->expects('findOneByCategoryAndBrand')
            ->with($this->categoryId, $this->brandId)
            ->andReturns();

        $this->entityManagerMock->expects('getReference')
            ->with(Brand::class, $this->brandId)
            ->andReturns($this->brandMock);

        $this->categoryMock->shouldReceive('getTitle')
            ->twice()
            ->with()
            ->andReturns($this->categoryName);

        $this->mockingTileAndMetaDescription();

        $this->addSeoSelectedFilterServiceMock->expects('perform');

        $this->updateOrCreateSeoSelectedFiltersService->updateOrCreate($this->categoryMock, $this->brands);
    }

    public function testItCanUpdateWhenExistSeoFilterBrand(): void
    {
        $this->categoryMock->expects('getId')
            ->with()
            ->andReturns($this->categoryId);

        $this->seoSelectedBrandFilterRepositoryMock->expects('findOneByCategoryAndBrand')
            ->with($this->categoryId, $this->brandId)
            ->andReturns($this->seoSelectedBrandFilterMock);

        $this->seoSelectedBrandFilterMock->expects('getTitle')
            ->with()
            ->andReturns(null);

        $this->seoSelectedBrandFilterMock->expects('getMetaDescription')
            ->with()
            ->andReturns(null);

        $this->categoryMock->expects('getTitle')
            ->with()
            ->andReturns($this->categoryName);

        $this->mockingTileAndMetaDescription();

        $this->seoSelectedBrandFilterMock->expects('setTitle')
            ->with(
                sprintf("%s %s %s %s", GenerateSoeMetadata::TITLE_PREFIX, $this->categoryName, $this->brandName, GenerateSoeMetadata::TITLE_POSTFIX)
            )
            ->andReturnSelf();

        $this->seoSelectedBrandFilterMock->expects('setMetaDescription')
            ->with(
                sprintf("%s %s %s %s", GenerateSoeMetadata::META_DESCRIPTION_PREFIX, $this->categoryName, $this->brandName, GenerateSoeMetadata::META_DESCRIPTION_POSTFIX)
            )
            ->andReturnSelf();

        $this->entityManagerMock->expects('flush')
            ->withNoArgs()
            ->andReturn();

        $this->updateOrCreateSeoSelectedFiltersService->updateOrCreate($this->categoryMock, $this->brands);
    }

    private function mockingTileAndMetaDescription(): void
    {
        $this->generateSoeMetadataMock->expects('title')
            ->with($this->categoryName, $this->brandName)
            ->andReturns(
                sprintf("%s %s %s %s", GenerateSoeMetadata::TITLE_PREFIX, $this->categoryName, $this->brandName, GenerateSoeMetadata::TITLE_POSTFIX)
            );

        $this->generateSoeMetadataMock->expects('metaDescription')
            ->with($this->categoryName, $this->brandName)
            ->andReturn(
                sprintf("%s %s %s %s", GenerateSoeMetadata::META_DESCRIPTION_PREFIX, $this->categoryName, $this->brandName, GenerateSoeMetadata::META_DESCRIPTION_POSTFIX)
            );
    }
}
