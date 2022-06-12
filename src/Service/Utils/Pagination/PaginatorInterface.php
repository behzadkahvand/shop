<?php

namespace App\Service\Utils\Pagination;

/**
 * Interface PaginatorInterface
 */
interface PaginatorInterface
{
    /**
     * @param $items
     * @param Pagination $pagination
     *
     * @return iterable
     */
    public function paginate($items, Pagination $pagination): iterable;
}
