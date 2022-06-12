<?php

namespace App\Service\Product\Search\Meta\Resolvers\Doctrine\Adapters;

use App\Service\Product\Search\DoctrineSearchData;
use App\Service\Product\Search\Meta\Resolvers\Doctrine\DoctrineSearchMetaResolverInterface;
use App\Service\Product\Search\SearchData;
use App\Service\Utils\Pagination\Pagination;
use App\Service\Utils\Pagination\PaginatorUtils;

/**
 * Class PaginationSearchMetaResolver
 */
final class PaginationSearchMetaResolver implements DoctrineSearchMetaResolverInterface
{
    private PaginatorUtils $paginatorUtils;

    public function __construct(PaginatorUtils $paginatorUtils)
    {
        $this->paginatorUtils = $paginatorUtils;
    }

    /**
     * @inheritDoc
     */
    public function resolve($query, SearchData $data, Pagination $pagination): array
    {
        if (!$data instanceof DoctrineSearchData) {
            return [];
        }

        $count = $this->paginatorUtils->getCount($query);

        return [
            'page'       => $pagination->getPage(),
            'perPage'    => $pagination->getLimit(),
            'totalItems' => $count,
            'totalPages' => (int) ceil($count / $pagination->getLimit()),
        ];
    }
}
