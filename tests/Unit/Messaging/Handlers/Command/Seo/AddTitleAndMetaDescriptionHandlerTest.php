<?php

namespace App\Tests\Unit\Messaging\Handlers\Command\Seo;

use App\Entity\Category;
use App\Messaging\Handlers\Command\Seo\AddTitleAndMetaDescriptionHandler;
use App\Messaging\Messages\Command\Seo\AddTitleAndMetaDescription;
use App\Repository\BrandRepository;
use App\Repository\CategoryRepository;
use App\Service\Seo\SeoSelectedFilter\UpdateOrCreateSeoSelectedFiltersService;
use App\Service\Utils\GenerateSoeMetadata;
use App\Tests\Unit\BaseUnitTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Mockery;
use Psr\Log\LoggerInterface;

class AddTitleAndMetaDescriptionHandlerTest extends BaseUnitTestCase
{
    private Mockery\LegacyMockInterface|EntityManagerInterface|Mockery\MockInterface|null $entityManagerMock;

    private ?AddTitleAndMetaDescriptionHandler $addTitleAndMetaDescriptionHandler;

    private Category|Mockery\LegacyMockInterface|Mockery\MockInterface|null $categoryMock;

    private ?string $categoryName;

    private ?int $categoryId;

    private Mockery\LegacyMockInterface|Mockery\MockInterface|CategoryRepository|null $categoryRepositoryMock;

    private Mockery\LegacyMockInterface|Mockery\MockInterface|BrandRepository|null $brandRepositoryMock;

    private GenerateSoeMetadata|Mockery\LegacyMockInterface|Mockery\MockInterface|null $generateSoeMetadataMock;

    private UpdateOrCreateSeoSelectedFiltersService|Mockery\LegacyMockInterface|Mockery\MockInterface|null $updateOrCreateSeoSelectedFiltersServiceMock;

    private LoggerInterface|Mockery\LegacyMockInterface|Mockery\MockInterface|null $loggerMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->entityManagerMock                           = Mockery::mock(EntityManagerInterface::class);
        $this->categoryRepositoryMock                      = Mockery::mock(CategoryRepository::class);
        $this->brandRepositoryMock                         = Mockery::mock(BrandRepository::class);
        $this->generateSoeMetadataMock                     = Mockery::mock(GenerateSoeMetadata::class);
        $this->updateOrCreateSeoSelectedFiltersServiceMock = Mockery::mock(UpdateOrCreateSeoSelectedFiltersService::class);

        $this->addTitleAndMetaDescriptionHandler = new AddTitleAndMetaDescriptionHandler(
            $this->entityManagerMock,
            $this->categoryRepositoryMock,
            $this->brandRepositoryMock,
            $this->generateSoeMetadataMock,
            $this->updateOrCreateSeoSelectedFiltersServiceMock
        );

        $this->categoryMock = Mockery::mock(Category::class);
        $this->loggerMock   = Mockery::mock(LoggerInterface::class);

        $faker              = Factory::create();
        $this->categoryName = $faker->title();
        $this->categoryId   = $faker->numberBetween(1, 10);
    }

    public function testItDoNothingWhenRowNotFound(): void
    {
        $addTitleAndMetaDescription = new AddTitleAndMetaDescription($this->categoryId);
        $this->entityManagerMock->expects('beginTransaction')->withNoArgs()->andReturn();
        $this->entityManagerMock->expects('getReference')
                                ->with(Category::class, $this->categoryId)
                                ->andReturnNull();

        $this->addTitleAndMetaDescriptionHandler->setLogger($this->loggerMock);

        $this->loggerMock->expects('error')
                         ->with(sprintf('Category %d is not exists for update pageTitle or metaDescription', $this->categoryId))
                         ->andReturn();

        $this->addTitleAndMetaDescriptionHandler->__invoke($addTitleAndMetaDescription);
    }

    public function testItCanAddTitleAndMetaDescriptionWhenBothAreNull(): void
    {
        $addTitleAndMetaDescription = new AddTitleAndMetaDescription($this->categoryId);

        $this->mockingCategoryAndBrand();
        $this->mockingTile();
        $this->mockingMetaDescription();

        $this->categoryMock->expects('getPageTitle')
                           ->with()
                           ->andReturn(null);

        $this->categoryMock->expects('getMetaDescription')
                           ->with()
                           ->andReturn(null);

        $this->categoryMock->expects('setPageTitle')
                           ->with(sprintf("%s %s %s", GenerateSoeMetadata::TITLE_PREFIX, $this->categoryName, GenerateSoeMetadata::TITLE_POSTFIX))
                           ->andReturnSelf();

        $this->categoryMock->expects('setMetaDescription')
                           ->with(sprintf("%s %s %s", GenerateSoeMetadata::META_DESCRIPTION_PREFIX, $this->categoryName, GenerateSoeMetadata::META_DESCRIPTION_POSTFIX))
                           ->andReturnSelf();

        $this->flushAndUpdateSeoFilters();

        $this->addTitleAndMetaDescriptionHandler->__invoke($addTitleAndMetaDescription);
    }

    public function testItCanAddTitleWhenJustTitleNull(): void
    {
        $addTitleAndMetaDescription = new AddTitleAndMetaDescription($this->categoryId);

        $this->mockingCategoryAndBrand();
        $this->mockingTile();

        $this->categoryMock->expects('getPageTitle')
                           ->with()
                           ->andReturn(null);

        $this->categoryMock->shouldReceive('getMetaDescription')
                           ->once()
                           ->with()
                           ->andReturn('test');

        $this->categoryMock->expects('setPageTitle')
                           ->with(sprintf("%s %s %s", GenerateSoeMetadata::TITLE_PREFIX, $this->categoryName, GenerateSoeMetadata::TITLE_POSTFIX))
                           ->andReturnSelf();

        $this->flushAndUpdateSeoFilters();

        $this->addTitleAndMetaDescriptionHandler->__invoke($addTitleAndMetaDescription);
    }

    public function testItCanAddMetaDescriptionWhenJustMetaDescriptionNull(): void
    {
        $addTitleAndMetaDescription = new AddTitleAndMetaDescription($this->categoryId);

        $this->mockingCategoryAndBrand();
        $this->mockingMetaDescription();

        $this->categoryMock->expects('getPageTitle')
                           ->with()
                           ->andReturn('test');

        $this->categoryMock->expects('getMetaDescription')
                           ->with()
                           ->andReturn(null);

        $this->categoryMock->expects('setMetaDescription')
                           ->with(sprintf("%s %s %s", GenerateSoeMetadata::META_DESCRIPTION_PREFIX, $this->categoryName, GenerateSoeMetadata::META_DESCRIPTION_POSTFIX))
                           ->andReturnSelf();

        $this->flushAndUpdateSeoFilters();

        $this->addTitleAndMetaDescriptionHandler->__invoke($addTitleAndMetaDescription);
    }

    private function mockingCategoryAndBrand(): void
    {
        $this->entityManagerMock->expects('beginTransaction')->withNoArgs()->andReturn();
        $this->entityManagerMock->expects('commit')->withNoArgs()->andReturn();

        $this->entityManagerMock->expects('getReference')
                                ->with(Category::class, $this->categoryId)
                                ->andReturns($this->categoryMock);

        $this->categoryRepositoryMock->expects('getCategoryLeafIdsForCategory')
                                     ->with($this->categoryMock)
                                     ->andReturn('1,3,4');

        $this->brandRepositoryMock->expects('getBrandsForProductSearch')
                                  ->with([1, 3, 4])
                                  ->andReturn([2, 6, 7]);

        $this->categoryMock->expects('getTitle')
                           ->with()
                           ->andReturn($this->categoryName);
    }

    private function mockingTile(): void
    {
        $this->generateSoeMetadataMock->expects('title')
                                      ->with($this->categoryName)
                                      ->andReturns(
                                          sprintf("%s %s %s", GenerateSoeMetadata::TITLE_PREFIX, $this->categoryName, GenerateSoeMetadata::TITLE_POSTFIX)
                                      );
    }

    private function mockingMetaDescription(): void
    {

        $this->generateSoeMetadataMock->expects('metaDescription')
                                      ->with($this->categoryName)
                                      ->andReturn(
                                          sprintf("%s %s %s", GenerateSoeMetadata::META_DESCRIPTION_PREFIX, $this->categoryName, GenerateSoeMetadata::META_DESCRIPTION_POSTFIX)
                                      );
    }

    private function flushAndUpdateSeoFilters(): void
    {
        $this->entityManagerMock->expects('flush')
                                ->withNoArgs()
                                ->andReturn();

        $this->updateOrCreateSeoSelectedFiltersServiceMock->expects('updateOrCreate')
                                                          ->with($this->categoryMock, [2, 6, 7])
                                                          ->andReturn();
    }
}
