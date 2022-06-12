<?php

namespace App\Tests\Unit\Service\Product\Search\Listeners\Doctrine;

use App\Dictionary\ConfigurationCodeDictionary;
use App\Entity\Configuration;
use App\Events\Product\Search\ProductSearchDataEvent;
use App\Service\Configuration\ConfigurationServiceInterface;
use App\Service\Product\Search\DoctrineSearchData;
use App\Service\Product\Search\Drivers\DoctrineProductSearchDriver;
use App\Service\Product\Search\Listeners\Doctrine\SellerExcludedCategoriesFilterListener;
use App\Service\Product\Search\SearchData;
use App\Service\Utils\Pagination\Pagination;
use App\Service\Utils\WebsiteAreaService;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class SellerExcludedCategoriesFilterListenerTest extends MockeryTestCase
{
    /**
     * @var WebsiteAreaService|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $websiteAreaMock;

    /**
     * @var ConfigurationServiceInterface|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $configurationServiceMock;

    /**
     * @var Configuration|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    protected $configurationMock;

    protected SellerExcludedCategoriesFilterListener $sellerExcludedCategoriesFilterListener;

    protected function setUp(): void
    {
        parent::setUp();

        $this->websiteAreaMock          = Mockery::mock(WebsiteAreaService::class);
        $this->configurationServiceMock = Mockery::mock(ConfigurationServiceInterface::class);
        $this->configurationMock        = Mockery::mock(Configuration::class);

        $this->sellerExcludedCategoriesFilterListener = new SellerExcludedCategoriesFilterListener(
            $this->websiteAreaMock,
            $this->configurationServiceMock
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function testItCanGetSubscribedEvents(): void
    {
        $result = $this->sellerExcludedCategoriesFilterListener::getSubscribedEvents();

        self::assertEquals([ProductSearchDataEvent::class => ['onProductSearchDataEvent', 1]], $result);
    }

    public function testItDoNothingWhenDriverIsInvalid(): void
    {
        $event = new ProductSearchDataEvent('invalid', new SearchData([], []), new Pagination());

        self::assertNull($this->sellerExcludedCategoriesFilterListener->onProductSearchDataEvent($event));

        $data = $event->getData();

        self::assertEquals([], $data->getFilters());
        self::assertEquals([], $data->getSorts());
    }

    public function testItDoNothingWhenWebsiteAreaIsNotSeller(): void
    {
        $event = new ProductSearchDataEvent(DoctrineProductSearchDriver::class, new SearchData([], []), new Pagination());

        $this->websiteAreaMock->shouldReceive('isSellerArea')
                              ->once()
                              ->withNoArgs()
                              ->andReturnFalse();

        self::assertNull($this->sellerExcludedCategoriesFilterListener->onProductSearchDataEvent($event));

        $data = $event->getData();

        self::assertEquals([], $data->getFilters());
        self::assertEquals([], $data->getSorts());
    }

    public function testItDoNothingWhenConfigurationIsNotSet(): void
    {
        $event = new ProductSearchDataEvent(DoctrineProductSearchDriver::class, new SearchData([], []), new Pagination());

        $this->websiteAreaMock->shouldReceive('isSellerArea')
                              ->once()
                              ->withNoArgs()
                              ->andReturnTrue();

        $this->configurationServiceMock->shouldReceive('findByCode')
                                       ->once()
                                       ->with(ConfigurationCodeDictionary::SELLER_SEARCH_EXCLUDED_CATEGORIES)
                                       ->andReturnNull();

        self::assertNull($this->sellerExcludedCategoriesFilterListener->onProductSearchDataEvent($event));

        $data = $event->getData();

        self::assertEquals([], $data->getFilters());
        self::assertEquals([], $data->getSorts());
    }

    public function testItDoNothingWhenConfigurationIsEmpty(): void
    {
        $event = new ProductSearchDataEvent(DoctrineProductSearchDriver::class, new SearchData([], []), new Pagination());

        $this->websiteAreaMock->shouldReceive('isSellerArea')
                              ->once()
                              ->withNoArgs()
                              ->andReturnTrue();

        $this->configurationServiceMock->shouldReceive('findByCode')
                                       ->once()
                                       ->with(ConfigurationCodeDictionary::SELLER_SEARCH_EXCLUDED_CATEGORIES)
                                       ->andReturn($this->configurationMock);

        $this->configurationMock->shouldReceive('getValue')
                                ->once()
                                ->withNoArgs()
                                ->andReturnNull();

        self::assertNull($this->sellerExcludedCategoriesFilterListener->onProductSearchDataEvent($event));

        $data = $event->getData();

        self::assertEquals([], $data->getFilters());
        self::assertEquals([], $data->getSorts());
    }

    public function testItCanAddExcludedCategoriesToSearchFilter(): void
    {
        $event = new ProductSearchDataEvent(
            DoctrineProductSearchDriver::class,
            new DoctrineSearchData([
                'category.id' => [
                    'in' => '33, 40, 69, 103, 187'
                ]
            ], [], 'category_code', 'title_search'),
            new Pagination()
        );

        $this->websiteAreaMock->shouldReceive('isSellerArea')
                              ->once()
                              ->withNoArgs()
                              ->andReturnTrue();

        $this->configurationServiceMock->shouldReceive('findByCode')
                                       ->once()
                                       ->with(ConfigurationCodeDictionary::SELLER_SEARCH_EXCLUDED_CATEGORIES)
                                       ->andReturn($this->configurationMock);

        $this->configurationMock->shouldReceive('getValue')
                                ->twice()
                                ->withNoArgs()
                                ->andReturn([131], [131]);

        self::assertNull($this->sellerExcludedCategoriesFilterListener->onProductSearchDataEvent($event));

        $data = $event->getData();

        self::assertEquals([
            'category.id' => [
                'in'  => '33, 40, 69, 103, 187',
                'nin' => '131'
            ]
        ], $data->getFilters());
        self::assertEquals([], $data->getSorts());
        self::assertEquals('category_code', $data->getCategoryCode());
        self::assertEquals('title_search', $data->getTitle());
    }
}
