<?php

namespace App\Tests\Unit\Service\Product\Search\Utils\SearchDataMapping\Adapters;

use App\Dictionary\ProductSearchDataMappingDictionary;
use App\Dictionary\WebsiteAreaDictionary;
use App\Service\Product\Search\Utils\SearchDataMapping\Adapters\StaticListSearchDataMappingAdapter;
use PHPUnit\Framework\TestCase;

/**
 * Class StaticListSearchDataMappingAdapterTest
 */
final class StaticListSearchDataMappingAdapterTest extends TestCase
{
    public function testItCheckCustomerAreaFilterExist()
    {
        $mapping = new StaticListSearchDataMappingAdapter();
        $customerFilters = ProductSearchDataMappingDictionary::CUSTOMER_AREA_MAPPING['filters'];

        foreach (array_keys($customerFilters) as $filter) {
            self::assertTrue($mapping->hasMappedFilter($filter, WebsiteAreaDictionary::AREA_CUSTOMER));
        }

        self::assertFalse($mapping->hasMappedFilter('invalidCustomerFilter', WebsiteAreaDictionary::AREA_CUSTOMER));
    }

    public function testItGetsCustomerAreaFilter()
    {
        $mapping = new StaticListSearchDataMappingAdapter();
        $customerFilters = ProductSearchDataMappingDictionary::CUSTOMER_AREA_MAPPING['filters'];

        foreach (array_keys($customerFilters) as $filter) {
            self::assertNotNull(
                $mapping->getMappedFilter($filter, WebsiteAreaDictionary::AREA_CUSTOMER)
            );
        }

        self::assertNull($mapping->getMappedFilter('invalidCustomerFilter', WebsiteAreaDictionary::AREA_CUSTOMER));
    }

    public function testItCheckSellerAreaFilterExist()
    {
        $mapping = new StaticListSearchDataMappingAdapter();
        $customerFilters = ProductSearchDataMappingDictionary::SELLER_AREA_MAPPING['filters'];

        foreach (array_keys($customerFilters) as $filter) {
            self::assertTrue($mapping->hasMappedFilter($filter, WebsiteAreaDictionary::AREA_SELLER));
        }

        self::assertFalse($mapping->hasMappedFilter('invalidSellerFilter', WebsiteAreaDictionary::AREA_CUSTOMER));
    }

    public function testItGetsSellerAreaFilter()
    {
        $mapping = new StaticListSearchDataMappingAdapter();
        $customerFilters = ProductSearchDataMappingDictionary::SELLER_AREA_MAPPING['filters'];

        foreach (array_keys($customerFilters) as $filter) {
            self::assertNotNull(
                $mapping->getMappedFilter($filter, WebsiteAreaDictionary::AREA_SELLER)
            );
        }

        self::assertNull($mapping->getMappedFilter('invalidSellerFilter', WebsiteAreaDictionary::AREA_CUSTOMER));
    }

    public function testItCheckCustomerAreaSortExist()
    {
        $mapping = new StaticListSearchDataMappingAdapter();
        $customerSorts = ProductSearchDataMappingDictionary::CUSTOMER_AREA_MAPPING['sorts'];

        foreach (array_keys($customerSorts) as $sort) {
            self::assertTrue($mapping->hasMappedSort($sort, WebsiteAreaDictionary::AREA_CUSTOMER));
        }

        self::assertFalse($mapping->hasMappedSort('invalidCustomerSort', WebsiteAreaDictionary::AREA_CUSTOMER));
    }

    public function testItGetsCustomerAreaSort()
    {
        $mapping = new StaticListSearchDataMappingAdapter();
        $customerSorts = ProductSearchDataMappingDictionary::CUSTOMER_AREA_MAPPING['sorts'];

        foreach (array_keys($customerSorts) as $sort) {
            self::assertNotNull(
                $mapping->getMappedSort($sort, WebsiteAreaDictionary::AREA_CUSTOMER)
            );
        }

        self::assertNull($mapping->getMappedSort('invalidCustomerSort', WebsiteAreaDictionary::AREA_CUSTOMER));
    }

    public function testItCheckSellerAreaSortExist()
    {
        $mapping = new StaticListSearchDataMappingAdapter();
        $customerSorts = ProductSearchDataMappingDictionary::SELLER_AREA_MAPPING['sorts'];

        foreach (array_keys($customerSorts) as $sort) {
            self::assertTrue($mapping->hasMappedSort($sort, WebsiteAreaDictionary::AREA_SELLER));
        }

        self::assertFalse($mapping->hasMappedSort('invalidSellerSort', WebsiteAreaDictionary::AREA_CUSTOMER));
    }

    public function testItGetsSellerAreaSort()
    {
        $mapping = new StaticListSearchDataMappingAdapter();
        $customerSorts = ProductSearchDataMappingDictionary::SELLER_AREA_MAPPING['sorts'];

        foreach (array_keys($customerSorts) as $sort) {
            self::assertNotNull(
                $mapping->getMappedSort($sort, WebsiteAreaDictionary::AREA_SELLER)
            );
        }

        self::assertNull($mapping->getMappedSort('invalidSellerSort', WebsiteAreaDictionary::AREA_CUSTOMER));
    }
}
