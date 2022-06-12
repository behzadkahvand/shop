<?php

namespace App\Events\Product\Search;

use App\Service\Product\Search\Queries\AbstractSearchQuery;
use App\Service\Product\Search\SearchData;
use App\Service\Utils\Pagination\Pagination;
use Symfony\Contracts\EventDispatcher\Event;

abstract class AbstractProductSearchQueryEvent extends Event
{
    private string $driverFQN;

    private AbstractSearchQuery $query;

    private SearchData $data;

    private Pagination $pagination;

    public function __construct(string $driverFQN, AbstractSearchQuery $query, SearchData $data, Pagination $pagination)
    {
        $this->driverFQN  = $driverFQN;
        $this->query      = $query;
        $this->data       = $data;
        $this->pagination = $pagination;
    }

    /**
     * @return string
     */
    public function getDriverFQN(): string
    {
        return $this->driverFQN;
    }

    /**
     * @return AbstractSearchQuery
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param AbstractSearchQuery $query
     *
     * @return AbstractProductSearchQueryEvent
     */
    public function setQuery(AbstractSearchQuery $query): AbstractProductSearchQueryEvent
    {
        $this->query = $query;

        return $this;
    }

    /**
     * @return SearchData
     */
    public function getData(): SearchData
    {
        return $this->data;
    }

    /**
     * @return Pagination
     */
    public function getPagination(): Pagination
    {
        return $this->pagination;
    }
}
