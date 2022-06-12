<?php

namespace App\Events\Product\Search;

use App\Service\Product\Search\SearchData;
use App\Service\Product\Search\SearchResult;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class ProductSearchResultEvent
 */
final class ProductSearchResultEvent extends Event
{
    private string $driverFQN;

    private SearchResult $searchResult;

    private SearchData $searchData;

    /**
     * ProductSearchResultEvent constructor.
     *
     * @param string       $driverFQN
     * @param SearchResult $searchResult
     * @param SearchData   $searchData
     */
    public function __construct(string $driverFQN, SearchResult $searchResult, SearchData $searchData)
    {
        $this->driverFQN = $driverFQN;
        $this->searchResult = $searchResult;
        $this->searchData = $searchData;
    }

    public function getDriverFQN(): string
    {
        return $this->driverFQN;
    }

    public function getSearchResult(): SearchResult
    {
        return $this->searchResult;
    }

    public function getSearchData(): SearchData
    {
        return $this->searchData;
    }
}
