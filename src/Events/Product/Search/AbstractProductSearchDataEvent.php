<?php

namespace App\Events\Product\Search;

use App\Service\Product\Search\SearchData;
use App\Service\Utils\Pagination\Pagination;
use Symfony\Contracts\EventDispatcher\Event;

abstract class AbstractProductSearchDataEvent extends Event
{
    private string $driverFQN;

    private SearchData $data;

    private Pagination $pagination;

    public function __construct(string $driverFQN, SearchData $data, Pagination $pagination)
    {
        $this->driverFQN  = $driverFQN;
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
     * @return SearchData
     */
    public function getData(): SearchData
    {
        return $this->data;
    }

    /**
     * @param SearchData $data
     *
     * @return AbstractProductSearchDataEvent
     */
    public function setData(SearchData $data): AbstractProductSearchDataEvent
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return Pagination
     */
    public function getPagination(): Pagination
    {
        return $this->pagination;
    }
}
