<?php

namespace App\Tests\Unit\Service\Product\Search\Utils\SearchDataMapping\Adapters;

use App\Dictionary\ProductSearchDataMappingDictionary;
use App\Dictionary\WebsiteAreaDictionary;
use App\Service\Product\Search\Utils\SearchDataMapping\Adapters\StaticListSellerSearchDataMappingAdapter;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class StaticListSellerSearchDataMappingAdapterTest
 */
final class StaticListSellerSearchDataMappingAdapterTest extends MockeryTestCase
{
    protected ?StaticListSellerSearchDataMappingAdapter $mapping;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mapping = new StaticListSellerSearchDataMappingAdapter();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->mapping = null;
    }

    public function testItCheckCustomerAreaFilterExist()
    {
        $customerFilters = ProductSearchDataMappingDictionary::CUSTOMER_SELLER_SEARCH_AREA_MAPPING['filters'];

        foreach (array_keys($customerFilters) as $filter) {
            self::assertTrue($this->mapping->hasMappedFilter($filter, WebsiteAreaDictionary::AREA_CUSTOMER));
        }

        self::assertFalse($this->mapping->hasMappedFilter('invalidCustomerFilter', WebsiteAreaDictionary::AREA_CUSTOMER));
    }

    public function testItGetsCustomerAreaFilter()
    {
        $customerFilters = ProductSearchDataMappingDictionary::CUSTOMER_SELLER_SEARCH_AREA_MAPPING['filters'];

        foreach (array_keys($customerFilters) as $filter) {
            self::assertNotNull(
                $this->mapping->getMappedFilter($filter, WebsiteAreaDictionary::AREA_CUSTOMER)
            );
        }

        self::assertNull($this->mapping->getMappedFilter('invalidCustomerFilter', WebsiteAreaDictionary::AREA_CUSTOMER));
    }

    public function testItCheckCustomerAreaSortExist()
    {
        $customerSorts = ProductSearchDataMappingDictionary::CUSTOMER_SELLER_SEARCH_AREA_MAPPING['sorts'];

        foreach (array_keys($customerSorts) as $sort) {
            self::assertTrue($this->mapping->hasMappedSort($sort, WebsiteAreaDictionary::AREA_CUSTOMER));
        }

        self::assertFalse($this->mapping->hasMappedSort('invalidCustomerSort', WebsiteAreaDictionary::AREA_CUSTOMER));
    }

    public function testItGetsCustomerAreaSort()
    {
        $customerSorts = ProductSearchDataMappingDictionary::CUSTOMER_SELLER_SEARCH_AREA_MAPPING['sorts'];

        foreach (array_keys($customerSorts) as $sort) {
            self::assertNotNull(
                $this->mapping->getMappedSort($sort, WebsiteAreaDictionary::AREA_CUSTOMER)
            );
        }

        self::assertNull($this->mapping->getMappedSort('invalidCustomerSort', WebsiteAreaDictionary::AREA_CUSTOMER));
    }
}
