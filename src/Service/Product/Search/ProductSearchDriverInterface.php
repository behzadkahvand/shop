<?php

namespace App\Service\Product\Search;

use App\Service\Product\Search\Queries\AbstractSearchQuery;
use App\Service\Utils\Pagination\Pagination;

/**
 * Interface ProductSearchServiceInterface
 */
interface ProductSearchDriverInterface
{
    /**
     * @param SearchData $data
     * @param Pagination $pagination
     *
     * @return AbstractSearchQuery
     */
    public function getQuery(SearchData $data, Pagination $pagination): AbstractSearchQuery;
}
