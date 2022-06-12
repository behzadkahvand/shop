<?php

namespace App\Service\Product\Search\Drivers;

use App\Entity\Product;
use App\Service\ORM\QueryBuilderFilterService;
use App\Service\Product\Search\DoctrineSearchData;
use App\Service\Product\Search\Exceptions\UnsupportedSearchDataTypeException;
use App\Service\Product\Search\Meta\SearchMetaResolverInterface;
use App\Service\Product\Search\ProductSearchDriverInterface;
use App\Service\Product\Search\Queries\AbstractSearchQuery;
use App\Service\Product\Search\Queries\Doctrine\QueryBuilderSearchQuery;
use App\Service\Product\Search\SearchData;
use App\Service\Utils\Pagination\Pagination;

/**
 * Class DoctrineProductSearchDriver
 */
class DoctrineProductSearchDriver implements ProductSearchDriverInterface
{
    private QueryBuilderFilterService $filterService;

    private SearchMetaResolverInterface $metaResolver;

    public function __construct(
        QueryBuilderFilterService $filterService,
        SearchMetaResolverInterface $metaResolver
    ) {
        $this->filterService = $filterService;
        $this->metaResolver  = $metaResolver;
    }

    /**
     * @inheritDoc
     */
    public function getQuery(SearchData $data, Pagination $pagination): AbstractSearchQuery
    {
        if (!$data instanceof DoctrineSearchData) {
            throw new UnsupportedSearchDataTypeException(DoctrineSearchData::class, get_class($data));
        }

        $queryBuilder = $this->filterService->filter(Product::class, [
            'filter' => $data->getFilters(),
            'sort'   => $data->getSorts(),
        ]);

        return new QueryBuilderSearchQuery(
            $queryBuilder,
            $this->metaResolver->resolve($queryBuilder, $data, $pagination)
        );
    }
}
