<?php

namespace App\Tests\Unit\Service\Seo\SeoSelectedFilter;

use App\DTO\Admin\Seo\SeoSelectedFilterData;
use App\Entity\Brand;
use App\Entity\Category;
use App\Entity\Seo\SeoSelectedBrandFilter;
use App\Service\Seo\SeoSelectedFilter\AddSeoSelectedFilterService;
use App\Service\Seo\SeoSelectedFilter\Exceptions\InvalidSeoSelectedEntityException;
use App\Service\Seo\SeoSelectedFilter\SeoSelectedFilterFactory;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class AddSeoSelectedFilterServiceTest extends MockeryTestCase
{
    /**
     * @var SeoSelectedFilterFactory|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $factoryMock;

    /**
     * @var EntityManagerInterface|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $em;

    /**
     * @var Category|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $categoryMock;

    /**
     * @var Brand|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $brandMock;

    /**
     * @var SeoSelectedBrandFilter|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $seoSelectedFilterMock;

    protected ?AddSeoSelectedFilterService $addSeoSelectedFilter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factoryMock           = Mockery::mock(SeoSelectedFilterFactory::class);
        $this->em                    = Mockery::mock(EntityManagerInterface::class);
        $this->categoryMock          = Mockery::mock(Category::class);
        $this->brandMock             = Mockery::mock(Brand::class);
        $this->seoSelectedFilterMock = Mockery::mock(SeoSelectedBrandFilter::class);

        $this->addSeoSelectedFilter = new AddSeoSelectedFilterService(
            $this->factoryMock,
            $this->em
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->factoryMock           = null;
        $this->em                    = null;
        $this->categoryMock          = null;
        $this->brandMock             = null;
        $this->seoSelectedFilterMock = null;
        $this->addSeoSelectedFilter  = null;
    }

    public function testItCanAddSeoSelectedFilter(): void
    {
        $data = new SeoSelectedFilterData();

        $data->setCategory($this->categoryMock)
             ->setBrand($this->brandMock)
             ->setTitle('seo selected filter title')
             ->setDescription('seo selected filter description')
             ->setMetaDescription('seo selected filter meta description')
             ->setStarred(true);

        $this->factoryMock->shouldReceive('getSeoSelectedFilter')
                          ->once()
                          ->with($this->brandMock)
                          ->andReturn($this->seoSelectedFilterMock);

        $this->seoSelectedFilterMock->shouldReceive('setEntity')
                                    ->once()
                                    ->with($this->brandMock)
                                    ->andReturn($this->seoSelectedFilterMock);
        $this->seoSelectedFilterMock->shouldReceive('setCategory')
                                    ->once()
                                    ->with($this->categoryMock)
                                    ->andReturn($this->seoSelectedFilterMock);
        $this->seoSelectedFilterMock->shouldReceive('setTitle')
                                    ->once()
                                    ->with($data->getTitle())
                                    ->andReturn($this->seoSelectedFilterMock);
        $this->seoSelectedFilterMock->shouldReceive('setDescription')
                                    ->once()
                                    ->with($data->getDescription())
                                    ->andReturn($this->seoSelectedFilterMock);
        $this->seoSelectedFilterMock->shouldReceive('setMetaDescription')
                                    ->once()
                                    ->with($data->getMetaDescription())
                                    ->andReturn($this->seoSelectedFilterMock);
        $this->seoSelectedFilterMock->shouldReceive('setStarred')
                                    ->once()
                                    ->with($data->isStarred())
                                    ->andReturn($this->seoSelectedFilterMock);

        $this->em->shouldReceive('beginTransaction')
                 ->once()
                 ->withNoArgs()
                 ->andReturn();
        $this->em->shouldReceive('persist')
                 ->once()
                 ->with($this->seoSelectedFilterMock)
                 ->andReturn();
        $this->em->shouldReceive('flush')
                 ->once()
                 ->withNoArgs()
                 ->andReturn();
        $this->em->shouldReceive('commit')
                 ->once()
                 ->withNoArgs()
                 ->andReturn();

        $result = $this->addSeoSelectedFilter->perform($data);

        self::assertEquals($this->seoSelectedFilterMock, $result);
    }

    public function testItThrowsExceptionWhenSeoSelectedEntityIsInvalid(): void
    {
        $data = new SeoSelectedFilterData();

        $data->setCategory($this->categoryMock)
             ->setBrand($this->brandMock)
             ->setTitle('seo selected filter title')
             ->setDescription('seo selected filter description')
             ->setMetaDescription('seo selected filter meta description')
             ->setStarred(true);

        $this->factoryMock->shouldReceive('getSeoSelectedFilter')
                          ->once()
                          ->with($this->brandMock)
                          ->andThrows(new InvalidSeoSelectedEntityException());

        $this->em->shouldReceive('beginTransaction')
                 ->once()
                 ->withNoArgs()
                 ->andReturn();
        $this->em->shouldReceive('close')
                 ->once()
                 ->withNoArgs()
                 ->andReturn();
        $this->em->shouldReceive('rollBack')
                 ->once()
                 ->withNoArgs()
                 ->andReturn();

        $this->expectException(InvalidSeoSelectedEntityException::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage('Seo selected entity is invalid!');

        $this->addSeoSelectedFilter->perform($data);
    }
}
