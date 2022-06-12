<?php

namespace App\Service\Utils\Pagination\Adapters;

use App\Service\Utils\Pagination\Pagination;
use App\Service\Utils\Pagination\PaginatorInterface;
use Knp\Component\Pager\PaginatorInterface as KnpPaginator;

/**
 * Class KnpPaginatorAdapter
 */
final class KnpPaginatorAdapter implements PaginatorInterface
{
    private KnpPaginator $knpPaginator;

    public function __construct(KnpPaginator $knpPaginator)
    {
        $this->knpPaginator = $knpPaginator;
    }

    /**
     * @inheritDoc
     */
    public function paginate($items, Pagination $pagination): iterable
    {
        return $this->knpPaginator->paginate($items, $pagination->getPage(), $pagination->getLimit(), [
            'wrap-queries' => true,
        ]);
    }
}
