<?php

namespace App\Service\Product\Search\Queries\Doctrine;

use App\Service\Product\Search\Queries\AbstractSearchQuery;
use Doctrine\ORM\QueryBuilder;

/**
 * Class DoctrineSearchQuery
 */
final class QueryBuilderSearchQuery extends AbstractSearchQuery
{
    private QueryBuilder $queryBuilder;

    private array $meta;

    public function __construct(QueryBuilder $queryBuilder, array $meta = [])
    {
        $this->queryBuilder = $queryBuilder;
        $this->meta         = $meta;
    }

    /**
     * @return QueryBuilder
     */
    public function getDoctrineQueryBuilder(): QueryBuilder
    {
        return $this->queryBuilder;
    }

    /**
     * @inheritDoc
     */
    public function getResult(): iterable
    {
        return $this->queryBuilder->getQuery()->getResult();
    }

    /**
     * @inheritDoc
     */
    public function getMeta(): array
    {
        return $this->meta;
    }

    public function getResultQuery(): QueryBuilder
    {
        return $this->queryBuilder;
    }
}
