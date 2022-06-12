<?php

namespace App\Service\Product\Search\Utils\SearchDataMapping;

/**
 * Interface SearchDataMappingInterface
 */
interface SearchDataMappingInterface
{
    /**
     * @param string $filter
     *
     * @return bool
     */
    public function hasMappedFilter(string $filter, string $area): bool;

    /**
     * @param string $filter
     *
     * @return string|null
     */
    public function getMappedFilter(string $filter, string $area): ?string;

    /**
     * @param string $sort
     *
     * @return bool
     */
    public function hasMappedSort(string $sort, string $area): bool;

    /**
     * @param string $sort
     *
     * @return string|null
     */
    public function getMappedSort(string $sort, string $area): ?string;
}
