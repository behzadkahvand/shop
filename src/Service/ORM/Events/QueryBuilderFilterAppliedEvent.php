<?php

namespace App\Service\ORM\Events;

use App\Service\ORM\QueryContext;
use Doctrine\ORM\QueryBuilder;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class QueryBuilderFilterAppliedEvent
 */
final class QueryBuilderFilterAppliedEvent extends Event
{
    /**
     * @var QueryBuilder
     */
    private QueryBuilder $queryBuilder;

    /**
     * @var QueryContext
     */
    private QueryContext $context;

    /**
     * @var string
     */
    private string $rootAlias;

    /**
     * @var array
     */
    private array $joinMap;

    /**
     * QueryBuilderFilterAppliedEvent constructor.
     *
     * @param QueryBuilder $queryBuilder
     * @param QueryContext $context
     * @param string $rootAlias
     * @param array $joinMap
     */
    public function __construct(QueryBuilder $queryBuilder, QueryContext $context, string $rootAlias, array $joinMap)
    {
        $this->queryBuilder = $queryBuilder;
        $this->context      = $context;
        $this->rootAlias    = $rootAlias;
        $this->joinMap      = $joinMap;
    }

    /**
     * @return QueryBuilder
     */
    public function getQueryBuilder(): QueryBuilder
    {
        return $this->queryBuilder;
    }

    /**
     * @return QueryContext
     */
    public function getContext(): QueryContext
    {
        return $this->context;
    }

    /**
     * @return string
     */
    public function getRootAlias(): string
    {
        return $this->rootAlias;
    }

    /**
     * @return array
     */
    public function getJoinMap(): array
    {
        return $this->joinMap;
    }
}
