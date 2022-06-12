<?php

namespace App\Service\Product\Search;

use App\Events\Product\Search\SellerProductSearchDataEvent;
use App\Events\Product\Search\SellerProductSearchQueryEvent;
use App\Service\Utils\Pagination\Pagination;
use App\Service\Utils\Pagination\PaginatorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class ProductSearchService
 */
final class SellerProductSearchService
{
    private ProductSearchDriverInterface $searchDriver;

    private EventDispatcherInterface $dispatcher;

    private PaginatorInterface $paginator;

    public function __construct(
        ProductSearchDriverInterface $driver,
        EventDispatcherInterface $dispatcher,
        PaginatorInterface $paginator
    ) {
        $this->searchDriver = $driver;
        $this->dispatcher   = $dispatcher;
        $this->paginator    = $paginator;
    }

    /**
     * @param SearchData $data
     * @param Pagination $pagination
     *
     * @return SearchResult
     */
    public function search(SearchData $data, Pagination $pagination): SearchResult
    {
        $driverFQN = get_class($this->searchDriver);

        $dataEvent = new SellerProductSearchDataEvent($driverFQN, $data, $pagination);
        $this->dispatcher->dispatch($dataEvent);

        $query = $this->searchDriver->getQuery($dataEvent->getData(), $dataEvent->getPagination());

        $queryEvent = new SellerProductSearchQueryEvent($driverFQN, $query, $data, $pagination);
        $this->dispatcher->dispatch($queryEvent);

        $searchQuery = $queryEvent->getQuery();
        $result      = $this->paginator->paginate($searchQuery->getResultQuery(), $pagination);

        return new SearchResult($result, $searchQuery->getMeta());
    }
}
