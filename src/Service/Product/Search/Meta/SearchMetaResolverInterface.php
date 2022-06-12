<?php

namespace App\Service\Product\Search\Meta;

use App\Service\Product\Search\SearchData;
use App\Service\Utils\Pagination\Pagination;

/**
 * Interface SearchMetaResolverInterface
 */
interface SearchMetaResolverInterface
{
    /**
     * @param mixed      $query
     * @param SearchData $data
     * @param Pagination $pagination
     *
     * @return array
     */
    public function resolve($query, SearchData $data, Pagination $pagination): array;
}
